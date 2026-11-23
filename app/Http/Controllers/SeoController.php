<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Services\Cache\ContentCache;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    private const CACHE_TTL_SECONDS = 3600;

    public function sitemap(): Response
    {
        $hostKey = md5(request()->getSchemeAndHttpHost());

        $xml = ContentCache::remember('seo', "sitemap_xml.{$hostKey}", self::CACHE_TTL_SECONDS, static function (): string {
            $posts = Post::query()
                ->published()
                ->select(['id', 'slug', 'published_at', 'updated_at'])
                ->latest('published_at')
                ->get();

            $categories = Category::query()
                ->whereHas('posts', fn ($query) => $query->published())
                ->select(['id', 'slug', 'updated_at'])
                ->orderBy('name')
                ->get();

            $tags = Tag::query()
                ->whereHas('posts', fn ($query) => $query->published())
                ->select(['id', 'slug', 'updated_at'])
                ->orderBy('name')
                ->get();

            return view('seo.sitemap', [
                'posts' => $posts,
                'categories' => $categories,
                'tags' => $tags,
                'latestPostDate' => $posts->first()?->updated_at ?? $posts->first()?->published_at,
            ])->render();
        });

        return response($xml)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function feed(): Response
    {
        $hostKey = md5(request()->getSchemeAndHttpHost());

        $xml = ContentCache::remember('seo', "feed_xml.{$hostKey}", self::CACHE_TTL_SECONDS, static function (): string {
            $posts = Post::query()
                ->published()
                ->with([
                    'author:id,name',
                    'categories:id,name,slug',
                ])
                ->latest('published_at')
                ->limit(20)
                ->get();

            return view('seo.feed', [
                'posts' => $posts,
                'lastBuildDate' => $posts->first()?->published_at ?? now(),
            ])->render();
        });

        return response($xml)->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
