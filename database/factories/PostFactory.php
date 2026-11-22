<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'wp_id' => null,
            'author_id' => User::factory(),
            'featured_media_id' => null,
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(),
            'content_html' => '<p>'.fake()->paragraphs(3, true).'</p>',
            'status' => ContentStatus::Draft,
            'published_at' => null,
            'meta_title' => $title,
            'meta_description' => fake()->sentence(15),
            'canonical_url' => null,
            'reading_time_minutes' => fake()->numberBetween(2, 12),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }
}
