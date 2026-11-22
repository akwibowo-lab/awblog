<?php

namespace App\Services;

use App\Models\Media;
use App\Services\WpImport\MediaProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUploadService
{
    public function __construct(
        private readonly MediaProcessor $processor,
    ) {}

    /**
     * Convert a Filament-uploaded file on disk into a Media record with WebP variants.
     */
    public function createFromStoredPath(
        string $storedPath,
        ?string $altText = null,
        ?string $caption = null,
        string $disk = 'public',
    ): Media {
        $storage = Storage::disk($disk);
        $sourcePath = $storage->path($storedPath);

        if (! is_file($sourcePath)) {
            throw new \RuntimeException("Uploaded media file not found: {$storedPath}");
        }

        $extension = strtolower(pathinfo($storedPath, PATHINFO_EXTENSION) ?: 'jpg');
        $baseName = Str::slug(pathinfo($storedPath, PATHINFO_FILENAME)) ?: 'media';
        $monthDir = now()->format('Y/m');
        $relativePath = "{$monthDir}/{$baseName}.{$extension}";

        $counter = 2;
        while ($storage->exists('media/'.$relativePath)) {
            $relativePath = "{$monthDir}/{$baseName}-{$counter}.{$extension}";
            $counter++;
        }

        $processed = $this->processor->process($sourcePath, $relativePath, $disk);

        if ($storedPath !== $processed['path']) {
            $storage->delete($storedPath);
        }

        return Media::query()->create([
            'disk' => $disk,
            'path' => $processed['path'],
            'filename' => $processed['filename'],
            'mime_type' => $processed['mime_type'],
            'size_bytes' => $processed['size_bytes'],
            'width' => $processed['width'],
            'height' => $processed['height'],
            'alt_text' => $altText,
            'caption' => $caption,
            'variants' => $processed['variants'],
        ]);
    }
}
