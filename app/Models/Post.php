<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Services\WpImport\ShortcodeCleaner;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'wp_id',
    'author_id',
    'featured_media_id',
    'title',
    'slug',
    'excerpt',
    'content_html',
    'status',
    'published_at',
    'meta_title',
    'meta_description',
    'canonical_url',
    'reading_time_minutes',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'wp_id' => 'integer',
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
            'reading_time_minutes' => 'integer',
        ];
    }

    /**
     * Author of the post.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Featured media attached to the post.
     *
     * @return BelongsTo<Media, $this>
     */
    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    /**
     * Categories assigned to the post.
     *
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Tags assigned to the post.
     *
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Scope a query to only include published posts up to now.
     *
     * @param  Builder<Post>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ContentStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Eager load relations needed for blog cards and detail views to avoid N+1 queries.
     *
     * @param  Builder<Post>  $query
     */
    public function scopeWithCardRelations(Builder $query): void
    {
        $query->with([
            'author:id,name',
            'featuredMedia',
            'categories:id,name,slug',
            'tags:id,name,slug',
        ]);
    }

    /**
     * Filter posts by keyword in title, excerpt, or content.
     *
     * @param  Builder<Post>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $normalized = trim((string) $term);

        if ($normalized === '') {
            return;
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $normalized).'%';

        $query->where(function (Builder $builder) use ($like): void {
            $builder->where('title', 'like', $like)
                ->orWhere('excerpt', 'like', $like)
                ->orWhere('content_html', 'like', $like);
        });
    }

    public function publicUrl(): string
    {
        return route('blog.show', ['slug' => $this->slug]);
    }

    public function seoTitle(): string
    {
        $metaTitle = trim((string) $this->meta_title);

        return $metaTitle !== '' ? $metaTitle : $this->title;
    }

    public function seoDescription(): string
    {
        $metaDescription = trim((string) $this->meta_description);

        if ($metaDescription !== '') {
            return $metaDescription;
        }

        return $this->displayExcerpt(160);
    }

    public function resolvedCanonicalUrl(): string
    {
        $canonical = trim((string) $this->canonical_url);

        return $canonical !== '' ? $canonical : $this->publicUrl();
    }

    public function displayExcerpt(int $limit = 160): string
    {
        $source = trim((string) $this->excerpt);

        if ($source === '') {
            $source = trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->content_html)) ?? '');
        }

        return Str::limit($source, $limit);
    }

    /**
     * Return rendered HTML with legacy shortcodes stripped and lazy loading guaranteed on inline images.
     */
    public function renderedContentHtml(): string
    {
        $html = app(ShortcodeCleaner::class)->clean((string) $this->content_html);

        if ($html === '' || ! str_contains($html, '<img')) {
            return $html;
        }

        return (string) preg_replace_callback('/<img\b[^>]*>/i', function (array $matches): string {
            $tag = $matches[0];

            if (! preg_match('/\bloading\s*=/i', $tag)) {
                $tag = preg_replace('/<img\b/i', '<img loading="lazy"', $tag, 1) ?? $tag;
            }

            if (! preg_match('/\bdecoding\s*=/i', $tag)) {
                $tag = preg_replace('/<img\b/i', '<img decoding="async"', $tag, 1) ?? $tag;
            }

            return $tag;
        }, $html);
    }
}
