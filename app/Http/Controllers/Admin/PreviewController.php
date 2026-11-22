<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Projects\ListPublishedProjects;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Response;

class PreviewController extends Controller
{
    public function post(Post $post): Response
    {
        app()->setLocale('id');
        Carbon::setLocale('id');

        $post->load(['author:id,name', 'featuredMedia', 'categories:id,name,slug', 'tags:id,name,slug']);

        return response()
            ->view('blog.show', [
                'post' => $post,
                'relatedPosts' => collect(),
                'seoTitle' => '[Preview] '.$post->seoTitle().' | Akut Wibowo',
                'seoDescription' => $post->seoDescription(),
                'canonicalUrl' => $post->resolvedCanonicalUrl(),
                'ogType' => 'article',
                'ogImage' => $post->featuredMedia?->variantUrl('large'),
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function project(Project $project, ListPublishedProjects $listPublishedProjects): Response
    {
        app()->setLocale('id');
        Carbon::setLocale('id');

        $project->load('featuredMedia');
        $normalized = $listPublishedProjects->normalizeModel($project);

        return response()
            ->view('projects.show', [
                'project' => $normalized,
                'otherProjects' => collect(),
                'seoTitle' => '[Preview] '.$normalized['title'].' | '.__('blog.site_name'),
                'seoDescription' => $normalized['summary'] !== ''
                    ? $normalized['summary']
                    : __('projects.seo.show_description', ['title' => $normalized['title']]),
                'canonicalUrl' => route('projects.show', ['slug' => $normalized['slug']]),
                'ogType' => 'website',
                'ogImage' => $normalized['media']['large_url'] ?? asset('branding/aw-orbit-dark.svg'),
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
