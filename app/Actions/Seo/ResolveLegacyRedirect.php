<?php

namespace App\Actions\Seo;

use App\Models\Post;
use App\Models\Redirect;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ResolveLegacyRedirect
{
    /**
     * Exact paths that must never be intercepted by legacy redirects.
     *
     * @var array<int, string>
     */
    private const IGNORED_EXACT = [
        '/sitemap.xml',
        '/feed',
    ];

    /**
     * Prefixes that must never be intercepted by legacy redirects.
     *
     * @var array<int, string>
     */
    private const IGNORED_PREFIXES = [
        '/admin',
        '/livewire',
        '/up',
        '/storage',
        '/build',
    ];

    /**
     * Resolve a legacy redirect target and HTTP status code for the incoming request.
     *
     * @return array{target: string, status: int}|null
     */
    public function execute(Request $request): ?array
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return null;
        }

        $normalizedPath = Redirect::normalizePath($request->getPathInfo());

        if ($this->shouldIgnore($normalizedPath)) {
            return null;
        }

        try {
            if ($rule = $this->matchDatabaseRule($normalizedPath)) {
                return $rule;
            }

            return $this->matchWordPressPattern($request, $normalizedPath);
        } catch (QueryException) {
            return null;
        }
    }

    private function shouldIgnore(string $normalizedPath): bool
    {
        if (in_array($normalizedPath, self::IGNORED_EXACT, true)) {
            return true;
        }

        foreach (self::IGNORED_PREFIXES as $prefix) {
            if ($normalizedPath === $prefix || str_starts_with($normalizedPath, $prefix.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{target: string, status: int}|null
     */
    private function matchDatabaseRule(string $normalizedPath): ?array
    {
        $redirect = Redirect::query()
            ->where('is_active', true)
            ->where('source_path', $normalizedPath)
            ->first();

        if (! $redirect) {
            return null;
        }

        $targetPath = Redirect::normalizePath($redirect->target_url);

        if (! str_starts_with($redirect->target_url, 'http') && $targetPath === $normalizedPath) {
            return null;
        }

        $redirect->increment('hits', 1, ['last_hit_at' => now()]);

        $status = in_array($redirect->status_code, [301, 302, 307, 308], true)
            ? $redirect->status_code
            : 301;

        return [
            'target' => $redirect->target_url,
            'status' => $status,
        ];
    }

    /**
     * @return array{target: string, status: int}|null
     */
    private function matchWordPressPattern(Request $request, string $normalizedPath): ?array
    {
        $wpPostId = $request->query('p');

        if (is_numeric($wpPostId) && (int) $wpPostId > 0) {
            $post = Post::query()
                ->published()
                ->where('wp_id', (int) $wpPostId)
                ->first(['id', 'slug']);

            if ($post) {
                return [
                    'target' => $post->publicUrl(),
                    'status' => 301,
                ];
            }
        }

        if (in_array($normalizedPath, ['/rss', '/rss2', '/feed/rss', '/feed/rss2', '/feed/atom', '/blog/feed', '/comments/feed'], true)) {
            return [
                'target' => route('seo.feed'),
                'status' => 301,
            ];
        }

        if (preg_match('#^/(category|tag)/([A-Za-z0-9\-]+)/feed$#', $normalizedPath, $matches)) {
            $routeName = $matches[1] === 'category' ? 'blog.category' : 'blog.tag';

            return [
                'target' => route($routeName, $matches[2]),
                'status' => 301,
            ];
        }

        if (preg_match('#^/\d{4}/\d{2}(?:/\d{2})?/([A-Za-z0-9\-]+)$#', $normalizedPath, $matches)) {
            $post = Post::query()
                ->published()
                ->where('slug', $matches[1])
                ->first(['id', 'slug']);

            if ($post) {
                return [
                    'target' => $post->publicUrl(),
                    'status' => 301,
                ];
            }
        }

        if (preg_match('#^/author/[^/]+$#', $normalizedPath)) {
            return [
                'target' => route('blog.index'),
                'status' => 301,
            ];
        }

        return null;
    }
}
