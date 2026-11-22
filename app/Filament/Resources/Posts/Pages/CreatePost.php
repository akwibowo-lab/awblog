<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Concerns\NormalizesPublishedTimestamp;
use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    use NormalizesPublishedTimestamp;

    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->ensurePublishedTimestamp($data);
    }
}
