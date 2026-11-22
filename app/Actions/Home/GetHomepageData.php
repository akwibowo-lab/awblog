<?php

namespace App\Actions\Home;

use App\Models\Post;
use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class GetHomepageData
{
    /**
     * Load published projects, latest blog posts, portfolio PDF URL, and dynamic homepage settings.
     *
     * @return array{
     *     projects: Collection<int, Project>,
     *     posts: Collection<int, Post>,
     *     portfolioPdfUrl: ?string,
     *     heroImageUrl: string,
     *     hasCustomHeroImage: bool,
     *     settings: array<string, mixed>
     * }
     */
    public function execute(): array
    {
        $settings = SiteSetting::getHomepage();

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

        $publicDisk = Storage::disk('public');

        $pdfCandidate = is_string($settings['portfolio_pdf_path'] ?? null) && $settings['portfolio_pdf_path'] !== ''
            ? $settings['portfolio_pdf_path']
            : 'portfolio/akut-wibowo-portfolio.pdf';

        $portfolioPdfUrl = $publicDisk->exists($pdfCandidate)
            ? asset('storage/'.ltrim($pdfCandidate, '/'))
            : null;

        $heroPath = $settings['hero_image_path'] ?? null;
        $hasCustomHeroImage = is_string($heroPath) && $heroPath !== '' && $publicDisk->exists($heroPath);
        $heroImageUrl = $hasCustomHeroImage
            ? asset('storage/'.ltrim($heroPath, '/'))
            : asset('branding/aw-orbit-dark.svg');

        return [
            'projects' => $projects,
            'posts' => $posts,
            'portfolioPdfUrl' => $portfolioPdfUrl,
            'heroImageUrl' => $heroImageUrl,
            'hasCustomHeroImage' => $hasCustomHeroImage,
            'settings' => $settings,
        ];
    }
}
