<?php

namespace App\Actions\Projects;

use Illuminate\Support\Collection;

class GetPublishedProject
{
    public function __construct(
        private readonly ListPublishedProjects $listPublishedProjects,
    ) {}

    /**
     * Resolve a single published project (or curated fallback) by slug along with other projects.
     *
     * @return array{project: array<string, mixed>, others: Collection<int, array<string, mixed>>}|null
     */
    public function execute(string $slug, ?string $locale = null): ?array
    {
        $allProjects = $this->listPublishedProjects->execute(locale: $locale);
        $project = $allProjects->firstWhere('slug', $slug);

        if ($project === null) {
            return null;
        }

        $others = $allProjects
            ->reject(fn (array $item): bool => $item['slug'] === $slug)
            ->take(2)
            ->values();

        return [
            'project' => $project,
            'others' => $others,
        ];
    }
}
