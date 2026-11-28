<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\ProjectLinkType;
use App\Enums\ProjectType;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'featured_media_id',
    'slug',
    'type',
    'title_id',
    'title_en',
    'summary_id',
    'summary_en',
    'description_id',
    'description_en',
    'live_url',
    'repo_url',
    'links',
    'tech_stack',
    'is_featured',
    'sort_order',
    'status',
    'published_at',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ProjectType::class,
            'links' => 'array',
            'tech_stack' => 'array',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * Featured media attached to the project.
     *
     * @return BelongsTo<Media, $this>
     */
    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    /**
     * Scope a query to only include published projects up to now.
     *
     * @param  Builder<Project>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ContentStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include featured projects.
     *
     * @param  Builder<Project>  $query
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * Scope a query to order projects by manual sort order then newest published.
     *
     * @param  Builder<Project>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderByDesc('published_at');
    }

    /**
     * Normalize structured links together with legacy live_url / repo_url fields.
     *
     * @return list<array{type: string, url: string, label: string}>
     */
    public function normalizedLinks(): array
    {
        $items = [];
        $seenUrls = [];

        if (is_array($this->links)) {
            foreach ($this->links as $link) {
                if (! is_array($link)) {
                    continue;
                }

                $url = trim((string) ($link['url'] ?? ''));
                if ($url === '' || isset($seenUrls[$url])) {
                    continue;
                }

                $type = ProjectLinkType::tryFrom((string) ($link['type'] ?? '')) ?? ProjectLinkType::Web;
                $label = trim((string) ($link['label'] ?? ''));

                $items[] = [
                    'type' => $type->value,
                    'url' => $url,
                    'label' => $label !== '' ? $label : $type->defaultPublicLabel(),
                ];
                $seenUrls[$url] = true;
            }
        }

        if (is_string($this->live_url) && trim($this->live_url) !== '' && ! isset($seenUrls[trim($this->live_url)])) {
            $url = trim($this->live_url);
            $items[] = [
                'type' => ProjectLinkType::Web->value,
                'url' => $url,
                'label' => __('projects.show.live_link'),
            ];
            $seenUrls[$url] = true;
        }

        if (is_string($this->repo_url) && trim($this->repo_url) !== '' && ! isset($seenUrls[trim($this->repo_url)])) {
            $url = trim($this->repo_url);
            $items[] = [
                'type' => ProjectLinkType::Github->value,
                'url' => $url,
                'label' => __('projects.show.repo_link'),
            ];
        }

        return $items;
    }
}
