<?php

namespace App\Actions\Projects;

use App\Enums\ProjectType;
use App\Models\Project;
use App\Services\Cache\ContentCache;
use Illuminate\Support\Collection;

class ListPublishedProjects
{
    private const CACHE_TTL_SECONDS = 1800;

    private const ALLOWED_DESCRIPTION_TAGS = '<p><br><h2><h3><h4><ul><ol><li><strong><em><a><code><pre><blockquote>';

    /**
     * Load published projects for the public projects index, falling back to curated defaults
     * when no published database records exist yet.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function execute(?ProjectType $typeFilter = null): Collection
    {
        $hostKey = md5(request()->getSchemeAndHttpHost());

        /** @var list<array<string, mixed>> $normalized */
        $normalized = ContentCache::remember(
            'projects',
            "published_normalized.{$hostKey}",
            self::CACHE_TTL_SECONDS,
            fn (): array => Project::query()
                ->published()
                ->with('featuredMedia')
                ->orderByDesc('is_featured')
                ->ordered()
                ->get()
                ->map(fn (Project $project): array => $this->normalizeModel($project))
                ->all()
        );

        if ($normalized !== []) {
            $collection = collect($normalized);

            if ($typeFilter !== null) {
                return $collection
                    ->filter(static fn (array $item): bool => $item['type'] === $typeFilter->value)
                    ->values();
            }

            return $collection;
        }

        $fallbacks = $this->fallbackItems();

        if ($typeFilter !== null) {
            return $fallbacks
                ->filter(fn (array $item): bool => $item['type'] === $typeFilter->value)
                ->values();
        }

        return $fallbacks;
    }

    /**
     * Normalize a Project Eloquent model into the shared view array shape.
     *
     * @return array<string, mixed>
     */
    public function normalizeModel(Project $project): array
    {
        $media = $project->featuredMedia;
        $mediumDims = $media?->variantDimensions('medium') ?? ['width' => null, 'height' => null];
        $largeDims = $media?->variantDimensions('large') ?? ['width' => null, 'height' => null];
        $descriptionHtml = trim((string) ($project->description_id ?? ''));

        if ($descriptionHtml === '' && trim((string) $project->summary_id) !== '') {
            $descriptionHtml = '<p>'.e($project->summary_id).'</p>';
        } else {
            $descriptionHtml = strip_tags($descriptionHtml, self::ALLOWED_DESCRIPTION_TAGS);
        }

        return [
            'slug' => $project->slug,
            'type' => $project->type->value,
            'type_label' => __('projects.types.'.$project->type->value),
            'title' => $project->title_id,
            'summary' => $project->summary_id ?? '',
            'description_html' => $descriptionHtml,
            'tech_stack' => is_array($project->tech_stack) ? array_values($project->tech_stack) : [],
            'live_url' => $project->live_url,
            'live_label' => $project->live_url ? __('projects.show.live_link') : null,
            'repo_url' => $project->repo_url,
            'links' => $project->normalizedLinks(),
            'is_featured' => (bool) $project->is_featured,
            'note' => null,
            'media' => $media ? [
                'url' => $media->variantUrl('medium'),
                'large_url' => $media->variantUrl('large'),
                'alt' => $media->altText($project->title_id),
                'width' => $mediumDims['width'],
                'height' => $mediumDims['height'],
                'large_width' => $largeDims['width'],
                'large_height' => $largeDims['height'],
            ] : null,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fallbackItems(): Collection
    {
        return collect(__('projects.fallback_items'))
            ->map(function (array $item): array {
                $type = (string) ($item['type'] ?? ProjectType::App->value);

                $links = [];
                if (! empty($item['live_url'])) {
                    $links[] = [
                        'type' => 'web',
                        'url' => (string) $item['live_url'],
                        'label' => (string) ($item['live_label'] ?? __('projects.show.live_link')),
                    ];
                }
                if (! empty($item['repo_url'])) {
                    $links[] = [
                        'type' => 'github',
                        'url' => (string) $item['repo_url'],
                        'label' => __('projects.show.repo_link'),
                    ];
                }

                return [
                    'slug' => (string) $item['slug'],
                    'type' => $type,
                    'type_label' => __('projects.types.'.$type),
                    'title' => (string) $item['title'],
                    'summary' => (string) ($item['summary'] ?? ''),
                    'description_html' => strip_tags((string) ($item['description_html'] ?? ''), self::ALLOWED_DESCRIPTION_TAGS),
                    'tech_stack' => is_array($item['tech_stack'] ?? null) ? array_values($item['tech_stack']) : [],
                    'live_url' => $item['live_url'] ?? null,
                    'live_label' => $item['live_label'] ?? null,
                    'repo_url' => $item['repo_url'] ?? null,
                    'links' => $links,
                    'is_featured' => (bool) ($item['is_featured'] ?? false),
                    'note' => $item['note'] ?? null,
                    'media' => null,
                ];
            })
            ->values();
    }
}
