<?php

namespace App\Models;

use App\Services\WpImport\MediaProcessor;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'wp_id',
    'wp_source_url',
    'disk',
    'path',
    'filename',
    'mime_type',
    'size_bytes',
    'width',
    'height',
    'alt_text',
    'caption',
    'variants',
])]
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'wp_id' => 'integer',
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'variants' => 'array',
        ];
    }

    /**
     * Posts using this media as their featured image.
     *
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'featured_media_id');
    }

    /**
     * Projects using this media as their featured image.
     *
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'featured_media_id');
    }

    /**
     * Resolve the public URL for a preferred WebP variant with fallback to the original path.
     */
    public function variantUrl(string $preferred = 'medium'): string
    {
        $variants = is_array($this->variants) ? $this->variants : [];
        $fallbackOrder = match ($preferred) {
            'thumbnail' => ['thumbnail', 'medium', 'large'],
            'large' => ['large', 'medium', 'thumbnail'],
            default => ['medium', 'large', 'thumbnail'],
        };

        $selectedPath = $this->path;

        foreach ($fallbackOrder as $size) {
            if (! empty($variants[$size]) && is_string($variants[$size])) {
                $selectedPath = $variants[$size];
                break;
            }
        }

        $disk = $this->disk ?: 'public';

        if ($disk === 'public') {
            return asset('storage/'.ltrim($selectedPath, '/'));
        }

        return Storage::disk($disk)->url($selectedPath);
    }

    /**
     * Calculate proportional display dimensions for a preferred variant.
     *
     * @return array{width: int|null, height: int|null}
     */
    public function variantDimensions(string $preferred = 'medium'): array
    {
        if (! $this->width || ! $this->height) {
            return ['width' => null, 'height' => null];
        }

        $maxWidth = MediaProcessor::VARIANT_WIDTHS[$preferred] ?? $this->width;

        if ($this->width <= $maxWidth) {
            return ['width' => $this->width, 'height' => $this->height];
        }

        return [
            'width' => $maxWidth,
            'height' => (int) round(($this->height / $this->width) * $maxWidth),
        ];
    }

    /**
     * Return accessible alt text with fallback.
     */
    public function altText(string $fallback = ''): string
    {
        $alt = trim((string) $this->alt_text);

        return $alt !== '' ? $alt : $fallback;
    }
}
