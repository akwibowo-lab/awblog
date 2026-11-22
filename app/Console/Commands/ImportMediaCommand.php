<?php

namespace App\Console\Commands;

use App\Services\WpImport\MediaImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportMediaCommand extends Command
{
    private const DEFAULT_ARCHIVE = 'D:\\wp-legacy\\akutwibowo-uploads-2026-11-12.tar.gz';

    protected $signature = 'wp:import-media
        {--archive= : Path to uploads .tar.gz archive or extracted uploads directory}
        {--connection=wp_legacy : Database connection name for WordPress staging}
        {--disk=public : Target filesystem disk}
        {--dry-run : Preview counts without writing files or updating posts}
        {--limit=0 : Maximum number of attachments to import (0 for all)}';

    protected $description = 'Import WordPress media, generate WebP variants, link featured images, and rewrite post URLs';

    public function handle(MediaImporter $importer): int
    {
        $archive = (string) ($this->option('archive') ?: self::DEFAULT_ARCHIVE);
        $connection = (string) $this->option('connection');
        $disk = (string) $this->option('disk');
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(0, (int) $this->option('limit'));

        try {
            $result = $importer->import($archive, $connection, $dryRun, $limit, $disk);
        } catch (Throwable $e) {
            $this->error('Media import failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $mode = $result->dryRun ? 'DRY RUN' : 'IMPORTED';
        $this->info("WordPress media {$mode} complete:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Media imported', $result->mediaImported],
                ['Media skipped', $result->mediaSkipped],
                ['Featured linked', $result->featuredLinked],
                ['Posts updated', $result->postsUpdated],
                ['URLs/lazy rewritten', $result->urlsRewritten],
                ['Missing files', count($result->missingFiles)],
            ]
        );

        return self::SUCCESS;
    }
}
