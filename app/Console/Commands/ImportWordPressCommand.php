<?php

namespace App\Console\Commands;

use App\Services\WpImport\WordPressImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportWordPressCommand extends Command
{
    protected $signature = 'wp:import
        {--connection=wp_legacy : Database connection name for WordPress staging}
        {--dry-run : Preview counts without writing to the Laravel database}
        {--limit=0 : Maximum number of posts to import (0 for all)}';

    protected $description = 'Import WordPress posts, categories, tags, and authors from staging wp_legacy';

    public function handle(WordPressImporter $importer): int
    {
        $connection = (string) $this->option('connection');
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(0, (int) $this->option('limit'));

        try {
            $result = $importer->import($connection, $dryRun, $limit);
        } catch (Throwable $e) {
            $this->error('WordPress import failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $mode = $result->dryRun ? 'DRY RUN' : 'IMPORTED';
        $this->info("WordPress {$mode} complete:");
        $this->table(
            ['Entity', 'Count'],
            [
                ['Users', $result->usersCreated],
                ['Categories', $result->categoriesSynced],
                ['Tags', $result->tagsSynced],
                ['Posts', $result->postsImported],
                ['Skipped', $result->postsSkipped],
            ]
        );

        return self::SUCCESS;
    }
}
