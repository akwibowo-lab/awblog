<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
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

        return response()
            ->view('seo.sitemap', [
                'posts' => $posts,
                'categories' => $categories,
                'tags' => $tags,
                'latestPostDate' => $posts->first()?->updated_at ?? $posts->first()?->published_at,
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function feed(): Response
    {
        $posts = Post::query()
            ->published()
            ->with([
                'author:id,name',
                'categories:id,name,slug',
            ])
            ->latest('published_at')
            ->limit(20)
            ->get();

        return response()
            ->view('seo.feed', [
                'posts' => $posts,
                'lastBuildDate' => $posts->first()?->published_at ?? now(),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
