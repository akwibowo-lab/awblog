<?php

namespace App\Http\Controllers;

use App\Actions\Blog\GetPublishedPost;
use App\Actions\Blog\ListPublishedPosts;
use App\Http\Requests\BlogIndexRequest;
use App\Models\Category;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    public function index(BlogIndexRequest $request, ListPublishedPosts $listPosts): View
    {
        $this->useIndonesianLocale();

        $search = $request->searchQuery();
        $posts = $listPosts->execute(search: $search);
        $categories = $listPosts->categoriesWithPublishedCount();
        $tags = $listPosts->popularTags();

        $title = $search !== ''
            ? __('blog.seo.search_title', ['query' => $search])
            : __('blog.seo.index_title');

        return view('blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
            'activeCategory' => null,
            'activeTag' => null,
            'searchQuery' => $search,
            'seoTitle' => $title,
            'seoDescription' => __('blog.seo.index_description'),
            'canonicalUrl' => route('blog.index'),
            'ogType' => 'website',
            'ogImage' => null,
        ]);
    }

    public function category(BlogIndexRequest $request, Category $category, ListPublishedPosts $listPosts): View
    {
        $this->useIndonesianLocale();

        $search = $request->searchQuery();
        $posts = $listPosts->execute(search: $search, category: $category);
        $categories = $listPosts->categoriesWithPublishedCount();
        $tags = $listPosts->popularTags();

        $description = trim((string) $category->description) !== ''
            ? trim((string) $category->description)
            : __('blog.seo.category_description', ['name' => $category->name]);

        return view('blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
            'activeCategory' => $category,
            'activeTag' => null,
            'searchQuery' => $search,
            'seoTitle' => __('blog.seo.category_title', ['name' => $category->name]),
            'seoDescription' => $description,
            'canonicalUrl' => route('blog.category', ['category' => $category->slug]),
            'ogType' => 'website',
            'ogImage' => null,
        ]);
    }

    public function tag(BlogIndexRequest $request, Tag $tag, ListPublishedPosts $listPosts): View
    {
        $this->useIndonesianLocale();

        $search = $request->searchQuery();
        $posts = $listPosts->execute(search: $search, tag: $tag);
        $categories = $listPosts->categoriesWithPublishedCount();
        $tags = $listPosts->popularTags();

        $description = trim((string) $tag->description) !== ''
            ? trim((string) $tag->description)
            : __('blog.seo.tag_description', ['name' => $tag->name]);

        return view('blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
            'activeCategory' => null,
            'activeTag' => $tag,
            'searchQuery' => $search,
            'seoTitle' => __('blog.seo.tag_title', ['name' => $tag->name]),
            'seoDescription' => $description,
            'canonicalUrl' => route('blog.tag', ['tag' => $tag->slug]),
            'ogType' => 'website',
            'ogImage' => null,
        ]);
    }

    public function show(string $slug, GetPublishedPost $getPost): View
    {
        $this->useIndonesianLocale();

        ['post' => $post, 'related' => $related] = $getPost->execute($slug);

        return view('blog.show', [
            'post' => $post,
            'relatedPosts' => $related,
            'seoTitle' => $post->seoTitle().' | Akut Wibowo',
            'seoDescription' => $post->seoDescription(),
            'canonicalUrl' => $post->resolvedCanonicalUrl(),
            'ogType' => 'article',
            'ogImage' => $post->featuredMedia?->variantUrl('large'),
        ]);
    }

    private function useIndonesianLocale(): void
    {
        app()->setLocale('id');
        Carbon::setLocale('id');
    }
}
