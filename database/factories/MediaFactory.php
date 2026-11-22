<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = Str::slug(fake()->words(3, true)).'.jpg';

        return [
            'wp_id' => null,
            'wp_source_url' => null,
            'disk' => 'public',
            'path' => 'media/2026/11/'.$filename,
            'filename' => $filename,
            'mime_type' => 'image/jpeg',
            'size_bytes' => fake()->numberBetween(20_000, 1_500_000),
            'width' => 1600,
            'height' => 900,
            'alt_text' => fake()->sentence(4),
            'caption' => null,
            'variants' => [
                'thumbnail' => 'media/2026/11/thumb-'.$filename.'.webp',
                'medium' => 'media/2026/11/med-'.$filename.'.webp',
                'large' => 'media/2026/11/lg-'.$filename.'.webp',
            ],
        ];
    }
}
