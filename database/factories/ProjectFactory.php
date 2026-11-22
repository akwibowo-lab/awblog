<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Enums\ProjectType;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titleId = fake()->unique()->sentence(3);

        return [
            'featured_media_id' => null,
            'slug' => Str::slug($titleId),
            'type' => ProjectType::App,
            'title_id' => $titleId,
            'title_en' => fake()->sentence(3),
            'summary_id' => fake()->sentence(12),
            'summary_en' => fake()->sentence(12),
            'description_id' => '<p>'.fake()->paragraph().'</p>',
            'description_en' => '<p>'.fake()->paragraph().'</p>',
            'live_url' => fake()->url(),
            'repo_url' => null,
            'tech_stack' => ['Laravel', 'Tailwind CSS', 'Alpine.js'],
            'is_featured' => false,
            'sort_order' => 0,
            'status' => ContentStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
