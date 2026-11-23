<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\ContentStatus;
use App\Filament\Concerns\NormalizesPublishedTimestamp;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\PostRevision;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    use NormalizesPublishedTimestamp;

    protected static string $resource = PostResource::class;

    public ?string $lastAutoSavedAt = null;

    private bool $skipRevisionOnSave = false;

    protected function getHeaderActions(): array
    {
        return [
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        if ($this->skipRevisionOnSave) {
            return;
        }

        /** @var Post $record */
        $record = $this->getRecord();
        $record->recordRevision(auth()->id());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->ensurePublishedTimestamp($data);
    }

    public function autoSaveDraft(): void
    {
        /** @var Post $record */
        $record = $this->getRecord();

        if ($record->status !== ContentStatus::Draft) {
            return;
        }

        $stateStatus = $this->data['status'] ?? null;
        $isStillDraft = $stateStatus === ContentStatus::Draft || $stateStatus === ContentStatus::Draft->value;

        if (! $isStillDraft) {
            return;
        }

        $this->skipRevisionOnSave = true;

        try {
            $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
            $this->lastAutoSavedAt = now()->format('H:i:s');
        } finally {
            $this->skipRevisionOnSave = false;
        }
    }

    public function restoreRevision(int $revisionId): void
    {
        /** @var Post $record */
        $record = $this->getRecord();

        /** @var PostRevision|null $revision */
        $revision = $record->revisions()->whereKey($revisionId)->first();

        if (! $revision) {
            return;
        }

        $record->recordRevision(auth()->id());

        $record->update([
            'title' => $revision->title,
            'slug' => $revision->slug,
            'excerpt' => $revision->excerpt,
            'content_html' => $revision->content_html,
            'meta_title' => $revision->meta_title,
            'meta_description' => $revision->meta_description,
            'focus_keyword' => $revision->focus_keyword,
        ]);

        $this->fillForm();

        Notification::make()
            ->title('Revisi berhasil dipulihkan.')
            ->success()
            ->send();
    }
}
