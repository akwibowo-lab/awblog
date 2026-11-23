<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Concerns\NormalizesPublishedTimestamp;
use App\Filament\Resources\Projects\Concerns\HandlesProjectThumbnail;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    use HandlesProjectThumbnail;
    use NormalizesPublishedTimestamp;

    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->ensurePublishedTimestamp($this->attachUploadedThumbnail($data));
    }
}
