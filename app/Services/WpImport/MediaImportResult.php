<?php

namespace App\Services\WpImport;

class MediaImportResult
{
    /**
     * @param  list<string>  $missingFiles
     */
    public function __construct(
        public bool $dryRun = false,
        public int $mediaImported = 0,
        public int $mediaSkipped = 0,
        public int $featuredLinked = 0,
        public int $postsUpdated = 0,
        public int $urlsRewritten = 0,
        public array $missingFiles = [],
    ) {}
}
