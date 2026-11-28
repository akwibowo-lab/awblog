<?php

namespace App\Http\Controllers;

use App\Actions\Projects\GetPublishedProject;
use App\Actions\Projects\ListPublishedProjects;
use App\Enums\ProjectType;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request, ListPublishedProjects $listPublishedProjects): View
    {
        $locale = app()->getLocale();
        Carbon::setLocale($locale);

        $typeParam = $request->query('type');
        $activeType = is_string($typeParam) ? ProjectType::tryFrom($typeParam) : null;
        $projects = $listPublishedProjects->execute($activeType, $locale);

        return view('projects.index', [
            'projects' => $projects,
            'activeType' => $activeType?->value,
            'types' => ProjectType::cases(),
            'seoTitle' => __('projects.seo.index_title'),
            'seoDescription' => __('projects.seo.index_description'),
            'canonicalUrl' => route('projects.index'),
            'ogType' => 'website',
            'ogImage' => asset('branding/aw-orbit-dark.svg'),
        ]);
    }

    public function show(string $slug, GetPublishedProject $getPublishedProject): View
    {
        $locale = app()->getLocale();
        Carbon::setLocale($locale);

        $result = $getPublishedProject->execute($slug, $locale);

        abort_if($result === null, 404);

        $project = $result['project'];
        $ogImage = $project['media']['large_url'] ?? asset('branding/aw-orbit-dark.svg');

        return view('projects.show', [
            'project' => $project,
            'otherProjects' => $result['others'],
            'seoTitle' => $project['title'].' | '.__('blog.site_name'),
            'seoDescription' => $project['summary'] !== ''
                ? $project['summary']
                : __('projects.seo.show_description', ['title' => $project['title']]),
            'canonicalUrl' => route('projects.show', ['slug' => $project['slug']]),
            'ogType' => 'website',
            'ogImage' => $ogImage,
        ]);
    }
}
