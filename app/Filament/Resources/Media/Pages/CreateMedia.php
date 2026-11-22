<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Services\MediaUploadService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $uploadedFile = (string) ($data['uploaded_file'] ?? '');

        return app(MediaUploadService::class)->createFromStoredPath(
            storedPath: $uploadedFile,
            altText: $data['alt_text'] ?? null,
            caption: $data['caption'] ?? null,
        );
    }
}
