<?php

namespace App\Filament\Concerns;

use App\Enums\ContentStatus;

trait NormalizesPublishedTimestamp
{
    /**
     * Auto-fill published_at when publishing without an explicit timestamp.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function ensurePublishedTimestamp(array $data): array
    {
        $status = $data['status'] ?? null;
        $isPublished = $status === ContentStatus::Published || $status === ContentStatus::Published->value;

        if ($isPublished && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
