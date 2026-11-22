<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Concerns\NormalizesPublishedTimestamp;
use App\Filament\Resources\Projects\Concerns\HandlesProjectThumbnail;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    use HandlesProjectThumbnail;
    use NormalizesPublishedTimestamp;

    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->ensurePublishedTimestamp($this->attachUploadedThumbnail($data));
    }
}
