<?php

namespace App\Filament\Resources\Projects\Concerns;

use App\Services\MediaUploadService;

trait HandlesProjectThumbnail
{
    /**
     * Convert an uploaded project thumbnail into a Media record and link featured_media_id.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function attachUploadedThumbnail(array $data): array
    {
        $uploadedPath = $data['thumbnail_upload'] ?? null;
        unset($data['thumbnail_upload']);

        if (is_string($uploadedPath) && $uploadedPath !== '') {
            $altText = $data['title_id'] ?? $data['title_en'] ?? 'Thumbnail proyek';
            $media = app(MediaUploadService::class)->createFromStoredPath(
                storedPath: $uploadedPath,
                altText: is_string($altText) ? $altText : null,
            );
            $data['featured_media_id'] = $media->id;
        }

        return $data;
    }
}
