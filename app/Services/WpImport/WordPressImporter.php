<?php

namespace App\Services\WpImport;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class WordPressImporter
{
    /**
     * WordPress post statuses imported into Laravel posts.
     *
     * @var list<string>
     */
    private const IMPORTABLE_STATUSES = [
        'publish',
        'draft',
        'pending',
        'future',
        'private',
        'trash',
    ];

    public function __construct(
        private readonly HtmlSanitizer $sanitizer,
    ) {}

    /**
     * Import WordPress users, categories, tags, and posts from the staging connection.
     */
    public function import(string $connectionName = 'wp_legacy', bool $dryRun = false, int $limit = 0): ImportResult
    {
        $source = DB::connection($connectionName);

        if ($dryRun) {
            return $this->preview($source, $limit);
        }

        return DB::transaction(function () use ($source, $limit): ImportResult {
            $result = new ImportResult;

            $userMap = $this->syncUsers($source, $result);
            $categoryMap = $this->syncCategories($source, $result);
            $tagMap = $this->syncTags($source, $result);
            $this->syncPosts($source, $userMap, $categoryMap, $tagMap, $result, $limit);

            return $result;
        });
    }

    private function preview(ConnectionInterface $source, int $limit): ImportResult
    {
        $postQuery = $source->table('posts')
            ->where('post_type', 'post')
            ->whereIn('post_status', self::IMPORTABLE_STATUSES);

        $postCount = $postQuery->count();

        if ($limit > 0) {
            $postCount = min($postCount, $limit);
        }

        $categoriesCount = $source->table('term_taxonomy')
            ->where('taxonomy', 'category')
            ->count();

        $tagsCount = $source->table('term_taxonomy')
            ->where('taxonomy', 'post_tag')
            ->count();

        $usersCount = $source->table('users')->count();

        return new ImportResult(
            usersCreated: $usersCount,
            categoriesSynced: $categoriesCount,
            tagsSynced: $tagsCount,
            postsImported: $postCount,
            postsSkipped: 0,
            dryRun: true,
        );
    }

    /**
     * @return array<int, int> Map of wp_user_id => laravel_user_id
     */
    private function syncUsers(ConnectionInterface $source, ImportResult $result): array
    {
        $wpUsers = $source->table('users')
            ->select(['ID', 'user_login', 'user_email', 'display_name'])
            ->get();

        $map = [];

        foreach ($wpUsers as $wpUser) {
            $email = trim((string) ($wpUser->user_email ?? ''));

            if ($email === '') {
                $email = Str::slug((string) $wpUser->user_login).'-wp'.$wpUser->ID.'@legacy.local';
            }

            $user = User::query()->where('email', $email)->first();

            if (! $user) {
                $user = User::query()->create([
                    'name' => trim((string) ($wpUser->display_name ?: $wpUser->user_login)) ?: 'Author '.$wpUser->ID,
                    'email' => $email,
                    'password' => Hash::make(Str::random(32)),
                ]);
                $result->usersCreated++;
            }

            $map[(int) $wpUser->ID] = $user->id;
        }

        return $map;
    }

    /**
     * @return array<int, int> Map of wp_term_id => laravel_category_id
     */
    private function syncCategories(ConnectionInterface $source, ImportResult $result): array
    {
        $rows = $source->table('terms as t')
            ->join('term_taxonomy as tt', 'tt.term_id', '=', 't.term_id')
            ->where('tt.taxonomy', 'category')
            ->select(['t.term_id', 't.name', 't.slug', 'tt.description', 'tt.parent'])
            ->get();

        $map = [];

        foreach ($rows as $row) {
            $name = $this->decodeText((string) $row->name);
            $slug = $this->resolveUniqueSlug(
                Category::class,
                urldecode((string) $row->slug),
                $name,
                (int) $row->term_id,
                'wp_term_id'
            );

            $category = Category::query()->updateOrCreate(
                ['wp_term_id' => (int) $row->term_id],
                [
                    'name' => Str::limit($name, 255, ''),
                    'slug' => $slug,
                    'description' => trim((string) $row->description) ?: null,
                ]
            );

            $map[(int) $row->term_id] = $category->id;
            $result->categoriesSynced++;
        }

        foreach ($rows as $row) {
            $parentWpId = (int) $row->parent;
            $categoryId = $map[(int) $row->term_id] ?? null;

            if ($categoryId === null || $parentWpId <= 0 || ! isset($map[$parentWpId])) {
                continue;
            }

            Category::query()
                ->where('id', $categoryId)
                ->update(['parent_id' => $map[$parentWpId]]);
        }

        return $map;
    }

    /**
     * @return array<int, int> Map of wp_term_id => laravel_tag_id
     */
    private function syncTags(ConnectionInterface $source, ImportResult $result): array
    {
        $rows = $source->table('terms as t')
            ->join('term_taxonomy as tt', 'tt.term_id', '=', 't.term_id')
            ->where('tt.taxonomy', 'post_tag')
            ->select(['t.term_id', 't.name', 't.slug', 'tt.description'])
            ->get();

        $map = [];

        foreach ($rows as $row) {
            $name = $this->decodeText((string) $row->name);
            $slug = $this->resolveUniqueSlug(
                Tag::class,
                urldecode((string) $row->slug),
                $name,
                (int) $row->term_id,
                'wp_term_id'
            );

            $tag = Tag::query()->updateOrCreate(
                ['wp_term_id' => (int) $row->term_id],
                [
                    'name' => Str::limit($name, 255, ''),
                    'slug' => $slug,
                    'description' => trim((string) $row->description) ?: null,
                ]
            );

            $map[(int) $row->term_id] = $tag->id;
            $result->tagsSynced++;
        }

        return $map;
    }

    /**
     * @param  array<int, int>  $userMap
     * @param  array<int, int>  $categoryMap
     * @param  array<int, int>  $tagMap
     */
    private function syncPosts(
        ConnectionInterface $source,
        array $userMap,
        array $categoryMap,
        array $tagMap,
        ImportResult $result,
        int $limit
    ): void {
        $query = $source->table('posts')
            ->where('post_type', 'post')
            ->whereIn('post_status', self::IMPORTABLE_STATUSES)
            ->orderBy('ID');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $wpPosts = $query->get();

        if ($wpPosts->isEmpty()) {
            return;
        }

        $wpIds = $wpPosts->pluck('ID')->map(fn ($id) => (int) $id)->all();
        $seoMeta = $this->loadSeoMeta($source, $wpIds);
        $termRelations = $this->loadTermRelations($source, $wpIds);

        foreach ($wpPosts as $wpPost) {
            $wpId = (int) $wpPost->ID;
            $title = $this->decodeText(strip_tags((string) $wpPost->post_title));

            if ($title === '') {
                $title = 'Untitled Post '.$wpId;
            }

            $slug = $this->resolveUniqueSlug(
                Post::class,
                urldecode((string) $wpPost->post_name),
                $title,
                $wpId,
                'wp_id'
            );

            $contentHtml = $this->sanitizer->sanitize((string) ($wpPost->post_content ?? ''));
            $excerpt = $this->decodeText(strip_tags((string) ($wpPost->post_excerpt ?? '')));
            $meta = $seoMeta[$wpId] ?? [];

            $post = Post::withTrashed()->updateOrCreate(
                ['wp_id' => $wpId],
                [
                    'author_id' => $userMap[(int) $wpPost->post_author] ?? null,
                    'title' => Str::limit($title, 255, ''),
                    'slug' => $slug,
                    'excerpt' => $excerpt !== '' ? $excerpt : null,
                    'content_html' => $contentHtml,
                    'status' => $this->mapStatus((string) $wpPost->post_status),
                    'published_at' => $this->parseDate($wpPost->post_date_gmt ?? null, $wpPost->post_date ?? null),
                    'meta_title' => isset($meta['title']) ? Str::limit($meta['title'], 255, '') : null,
                    'meta_description' => $meta['description'] ?? null,
                    'canonical_url' => isset($meta['canonical']) ? Str::limit($meta['canonical'], 500, '') : null,
                    'reading_time_minutes' => $this->estimateReadingTime($contentHtml),
                ]
            );

            if ((string) $wpPost->post_status === 'trash' && ! $post->trashed()) {
                $post->delete();
            } elseif ((string) $wpPost->post_status !== 'trash' && $post->trashed()) {
                $post->restore();
            }

            $categoryIds = [];
            foreach ($termRelations[$wpId]['category'] ?? [] as $wpTermId) {
                if (isset($categoryMap[$wpTermId])) {
                    $categoryIds[] = $categoryMap[$wpTermId];
                }
            }
            $post->categories()->sync($categoryIds);

            $tagIds = [];
            foreach ($termRelations[$wpId]['post_tag'] ?? [] as $wpTermId) {
                if (isset($tagMap[$wpTermId])) {
                    $tagIds[] = $tagMap[$wpTermId];
                }
            }
            $post->tags()->sync($tagIds);

            $result->postsImported++;
        }
    }

    /**
     * @param  list<int>  $wpIds
     * @return array<int, array{title?: string, description?: string, canonical?: string}>
     */
    private function loadSeoMeta(ConnectionInterface $source, array $wpIds): array
    {
        $metaKeys = [
            '_yoast_wpseo_title',
            '_yoast_wpseo_metadesc',
            '_yoast_wpseo_canonical',
            'rank_math_title',
            'rank_math_description',
            'rank_math_canonical_url',
        ];

        $rows = $source->table('postmeta')
            ->whereIn('post_id', $wpIds)
            ->whereIn('meta_key', $metaKeys)
            ->select(['post_id', 'meta_key', 'meta_value'])
            ->get();

        $meta = [];

        foreach ($rows as $row) {
            $value = trim((string) ($row->meta_value ?? ''));

            if ($value === '') {
                continue;
            }

            $postId = (int) $row->post_id;
            $key = match ($row->meta_key) {
                '_yoast_wpseo_title', 'rank_math_title' => 'title',
                '_yoast_wpseo_metadesc', 'rank_math_description' => 'description',
                '_yoast_wpseo_canonical', 'rank_math_canonical_url' => 'canonical',
                default => null,
            };

            if ($key !== null && ! isset($meta[$postId][$key])) {
                $meta[$postId][$key] = $this->decodeText($value);
            }
        }

        return $meta;
    }

    /**
     * @param  list<int>  $wpIds
     * @return array<int, array<string, list<int>>>
     */
    private function loadTermRelations(ConnectionInterface $source, array $wpIds): array
    {
        $rows = $source->table('term_relationships as tr')
            ->join('term_taxonomy as tt', 'tt.term_taxonomy_id', '=', 'tr.term_taxonomy_id')
            ->whereIn('tr.object_id', $wpIds)
            ->whereIn('tt.taxonomy', ['category', 'post_tag'])
            ->select(['tr.object_id', 'tt.taxonomy', 'tt.term_id'])
            ->get();

        $relations = [];

        foreach ($rows as $row) {
            $relations[(int) $row->object_id][(string) $row->taxonomy][] = (int) $row->term_id;
        }

        return $relations;
    }

    private function mapStatus(string $wpStatus): ContentStatus
    {
        return match ($wpStatus) {
            'publish' => ContentStatus::Published,
            'private', 'trash' => ContentStatus::Archived,
            default => ContentStatus::Draft,
        };
    }

    private function parseDate(?string $gmtDate, ?string $localDate): ?CarbonImmutable
    {
        foreach ([$gmtDate, $localDate] as $candidate) {
            if (! is_string($candidate) || $candidate === '' || str_starts_with($candidate, '0000-00-00')) {
                continue;
            }

            try {
                return CarbonImmutable::parse($candidate, 'UTC');
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }

    private function estimateReadingTime(string $html): int
    {
        $wordCount = str_word_count(strip_tags($html));

        return max(1, (int) ceil($wordCount / 200));
    }

    private function decodeText(string $text): string
    {
        return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * Preserve the original WordPress slug unless another record already owns it.
     *
     * @param  class-string<Model>  $modelClass
     */
    private function resolveUniqueSlug(
        string $modelClass,
        string $rawSlug,
        string $fallbackTitle,
        int $wpId,
        string $wpColumn
    ): string {
        $base = trim($rawSlug) !== '' ? trim($rawSlug) : Str::slug($fallbackTitle);

        if ($base === '') {
            $base = 'item-'.$wpId;
        }

        $base = Str::limit($base, 240, '');
        $candidate = $base;

        $query = method_exists($modelClass, 'withTrashed')
            ? $modelClass::withTrashed()
            : $modelClass::query();

        $conflict = $query
            ->where('slug', $candidate)
            ->where(function ($q) use ($wpColumn, $wpId): void {
                $q->whereNull($wpColumn)->orWhere($wpColumn, '!=', $wpId);
            })
            ->exists();

        if ($conflict) {
            $candidate = Str::limit($base, 230, '').'-'.$wpId;
        }

        return $candidate;
    }
}
