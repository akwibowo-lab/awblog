<?php

namespace App\Actions\Home;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class GetHomepageData
{
    /**
     * Load published projects and latest blog posts for the portfolio homepage.
     *
     * @return array{projects: Collection<int, Project>, posts: Collection<int, Post>, portfolioPdfUrl: ?string}
     */
    public function execute(): array
    {
        $projects = Project::query()
            ->published()
            ->with('featuredMedia')
            ->orderByDesc('is_featured')
            ->ordered()
            ->limit(6)
            ->get();

        $posts = Post::query()
            ->published()
            ->withCardRelations()
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $portfolioPdfPath = 'portfolio/akut-wibowo-portfolio.pdf';
        $portfolioPdfUrl = is_file(storage_path('app/public/'.$portfolioPdfPath))
            ? asset('storage/'.$portfolioPdfPath)
            : null;

        return [
            'projects' => $projects,
            'posts' => $posts,
            'portfolioPdfUrl' => $portfolioPdfUrl,
        ];
    }
}
