<?php

namespace App\Services\WpImport;

class ImportResult
{
    public function __construct(
        public int $usersCreated = 0,
        public int $categoriesSynced = 0,
        public int $tagsSynced = 0,
        public int $postsImported = 0,
        public int $postsSkipped = 0,
        public bool $dryRun = false,
    ) {}
}
