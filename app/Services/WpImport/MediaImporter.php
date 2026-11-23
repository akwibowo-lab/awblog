<?php

namespace App\Services\WpImport;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class MediaImporter
{
    public function __construct(
        private readonly MediaProcessor $processor,
        private readonly ContentUrlRewriter $rewriter,
    ) {}

    /**
     * Import WordPress attachments from an uploads directory or .tar.gz archive,
     * link featured images, and rewrite /wp-content/uploads/ URLs in posts.
     */
    public function import(
        string $sourcePath,
        string $connectionName = 'wp_legacy',
        bool $dryRun = false,
        int $limit = 0,
        string $disk = 'public',
    ): MediaImportResult {
        if (! file_exists($sourcePath)) {
            throw new RuntimeException("Media source not found: {$sourcePath}");
        }

        $source = DB::connection($connectionName);

        if ($dryRun) {
            return $this->preview($source, $limit);
        }

        $tempExtractDir = null;
        $uploadsRoot = $this->resolveUploadsRoot($sourcePath, $tempExtractDir);

        try {
            return $this->executeImport($source, $uploadsRoot, $limit, $disk);
        } finally {
            if ($tempExtractDir !== null && is_dir($tempExtractDir)) {
                File::deleteDirectory($tempExtractDir);
            }
        }
    }

    private function preview(ConnectionInterface $source, int $limit): MediaImportResult
    {
        $attachmentCount = $source->table('posts')
            ->where('post_type', 'attachment')
            ->count();

        if ($limit > 0) {
            $attachmentCount = min($attachmentCount, $limit);
        }

        $thumbnailCount = $source->table('postmeta')
            ->where('meta_key', '_thumbnail_id')
            ->count();

        $postsWithUploads = Post::query()
            ->where('content_html', 'like', '%wp-content/uploads%')
            ->count();

        return new MediaImportResult(
            dryRun: true,
            mediaImported: $attachmentCount,
            featuredLinked: $thumbnailCount,
            postsUpdated: $postsWithUploads,
        );
    }

    private function executeImport(
        ConnectionInterface $source,
        string $uploadsRoot,
        int $limit,
        string $disk,
    ): MediaImportResult {
        $result = new MediaImportResult;

        $attachmentsQuery = $source->table('posts')
            ->where('post_type', 'attachment')
            ->orderBy('ID');

        if ($limit > 0) {
            $attachmentsQuery->limit($limit);
        }

        $attachments = $attachmentsQuery->get(['ID', 'guid', 'post_title', 'post_excerpt', 'post_mime_type']);
        $attachmentIds = $attachments->pluck('ID')->all();

        $metaByPostId = $this->loadAttachmentMeta($source, $attachmentIds);
        $mediaByWpId = [];
        $mediaByRelPath = [];

        foreach ($attachments as $attachment) {
            $wpId = (int) $attachment->ID;
            $meta = $metaByPostId[$wpId] ?? [];
            $relPath = $this->resolveRelativePath($meta['_wp_attached_file'] ?? null, (string) $attachment->guid);

            if ($relPath === null) {
                continue;
            }

            $altText = trim((string) ($meta['_wp_attachment_image_alt'] ?? ''));
            $caption = trim((string) $attachment->post_excerpt);
            $sourceUrl = trim((string) $attachment->guid);

            $media = $this->importSingleMedia(
                wpId: $wpId,
                sourceUrl: $sourceUrl !== '' ? $sourceUrl : null,
                relativePath: $relPath,
                altText: $altText !== '' ? Str::limit($altText, 255, '') : null,
                caption: $caption !== '' ? $caption : null,
                uploadsRoot: $uploadsRoot,
                disk: $disk,
                result: $result,
            );

            if ($media instanceof Media) {
                $mediaByWpId[$wpId] = $media;
                $this->registerMediaLookupKeys($mediaByRelPath, $relPath, $media);
            }
        }

        $this->linkFeaturedMedia($source, $mediaByWpId, $result);
        $this->importInlineUploadsFromPosts($uploadsRoot, $disk, $mediaByRelPath, $result);
        $this->rewritePostContents($mediaByRelPath, $result);

        return $result;
    }

    /**
     * @param  list<int|string>  $attachmentIds
     * @return array<int, array<string, string>>
     */
    private function loadAttachmentMeta(ConnectionInterface $source, array $attachmentIds): array
    {
        if ($attachmentIds === []) {
            return [];
        }

        $grouped = [];

        foreach (array_chunk($attachmentIds, 500) as $chunk) {
            $rows = $source->table('postmeta')
                ->whereIn('post_id', $chunk)
                ->whereIn('meta_key', ['_wp_attached_file', '_wp_attachment_image_alt'])
                ->get(['post_id', 'meta_key', 'meta_value']);

            foreach ($rows as $row) {
                $grouped[(int) $row->post_id][(string) $row->meta_key] = (string) ($row->meta_value ?? '');
            }
        }

        return $grouped;
    }

    /**
     * @param  array<string, Media>  $mediaByRelPath
     */
    private function importSingleMedia(
        ?int $wpId,
        ?string $sourceUrl,
        string $relativePath,
        ?string $altText,
        ?string $caption,
        string $uploadsRoot,
        string $disk,
        MediaImportResult $result,
    ): ?Media {
        $existing = $wpId !== null
            ? Media::query()->where('wp_id', $wpId)->first()
            : Media::query()->where('path', 'media/'.ltrim($relativePath, '/'))->first();

        $storage = Storage::disk($disk);

        if ($existing instanceof Media && $storage->exists($existing->path)) {
            $result->mediaSkipped++;

            return $existing;
        }

        $sourceFile = $this->findSourceFile($uploadsRoot, $relativePath);
        if ($sourceFile === null) {
            $result->missingFiles[] = $relativePath;

            return null;
        }

        $processed = $this->processor->process($sourceFile, $relativePath, $disk);

        $attributes = [
            'wp_source_url' => $sourceUrl,
            'disk' => $disk,
            'path' => $processed['path'],
            'filename' => $processed['filename'],
            'mime_type' => $processed['mime_type'],
            'size_bytes' => $processed['size_bytes'],
            'width' => $processed['width'],
            'height' => $processed['height'],
            'alt_text' => $altText,
            'caption' => $caption,
            'variants' => $processed['variants'],
        ];

        $media = $wpId !== null
            ? Media::query()->updateOrCreate(['wp_id' => $wpId], $attributes)
            : Media::query()->updateOrCreate(['path' => $processed['path']], $attributes);

        $result->mediaImported++;

        return $media;
    }

    /**
     * @param  array<int, Media>  $mediaByWpId
     */
    private function linkFeaturedMedia(
        ConnectionInterface $source,
        array $mediaByWpId,
        MediaImportResult $result,
    ): void {
        $thumbnailRows = $source->table('postmeta')
            ->where('meta_key', '_thumbnail_id')
            ->get(['post_id', 'meta_value']);

        $wpPostToMediaId = [];
        foreach ($thumbnailRows as $row) {
            $attachmentWpId = (int) $row->meta_value;
            if (isset($mediaByWpId[$attachmentWpId])) {
                $wpPostToMediaId[(int) $row->post_id] = $mediaByWpId[$attachmentWpId]->id;
            }
        }

        if ($wpPostToMediaId === []) {
            return;
        }

        $posts = Post::query()
            ->whereIn('wp_id', array_keys($wpPostToMediaId))
            ->get(['id', 'wp_id', 'featured_media_id']);

        foreach ($posts as $post) {
            $targetMediaId = $wpPostToMediaId[(int) $post->wp_id] ?? null;
            if ($targetMediaId !== null && $post->featured_media_id !== $targetMediaId) {
                $post->featured_media_id = $targetMediaId;
                $post->save();
                $result->featuredLinked++;
            }
        }
    }

    /**
     * Import any /wp-content/uploads/... files referenced in post HTML that were not in c71gfp8_posts attachments.
     *
     * @param  array<string, Media>  $mediaByRelPath
     */
    private function importInlineUploadsFromPosts(
        string $uploadsRoot,
        string $disk,
        array &$mediaByRelPath,
        MediaImportResult $result,
    ): void {
        Post::query()
            ->where('content_html', 'like', '%wp-content/uploads/%')
            ->chunkById(50, function ($posts) use ($uploadsRoot, $disk, &$mediaByRelPath, $result): void {
                foreach ($posts as $post) {
                    foreach (ContentUrlRewriter::extractUploadPaths((string) $post->content_html) as $relPath) {
                        $stripped = ContentUrlRewriter::stripDimensionSuffix($relPath);
                        if (isset($mediaByRelPath[strtolower($relPath)]) || isset($mediaByRelPath[strtolower($stripped)])) {
                            continue;
                        }

                        // Prefer importing the original un-suffixed file if present in uploadsRoot.
                        $targetRel = $this->findSourceFile($uploadsRoot, $stripped) !== null ? $stripped : $relPath;

                        $media = $this->importSingleMedia(
                            wpId: null,
                            sourceUrl: null,
                            relativePath: $targetRel,
                            altText: null,
                            caption: null,
                            uploadsRoot: $uploadsRoot,
                            disk: $disk,
                            result: $result,
                        );

                        if ($media instanceof Media) {
                            $this->registerMediaLookupKeys($mediaByRelPath, $targetRel, $media);
                            $this->registerMediaLookupKeys($mediaByRelPath, $relPath, $media);
                        }
                    }
                }
            });
    }

    /**
     * @param  array<string, Media>  $mediaByRelPath
     */
    private function rewritePostContents(array $mediaByRelPath, MediaImportResult $result): void
    {
        Post::query()
            ->where(function ($query): void {
                $query->where('content_html', 'like', '%wp-content/uploads/%')
                    ->orWhere('content_html', 'like', '%<img%');
            })
            ->chunkById(50, function ($posts) use ($mediaByRelPath, $result): void {
                foreach ($posts as $post) {
                    $rewritten = $this->rewriter->rewrite((string) $post->content_html, $mediaByRelPath);
                    if ($rewritten['rewrites'] > 0 && $rewritten['html'] !== $post->content_html) {
                        $post->content_html = $rewritten['html'];
                        $post->save();
                        $result->postsUpdated++;
                        $result->urlsRewritten += $rewritten['rewrites'];
                    }
                }
            });
    }

    /**
     * @param  array<string, Media>  $mediaByRelPath
     */
    private function registerMediaLookupKeys(array &$mediaByRelPath, string $relativePath, Media $media): void
    {
        $normalized = ltrim(str_replace('\\', '/', $relativePath), '/');
        $mediaByRelPath[strtolower($normalized)] = $media;
        $mediaByRelPath[strtolower(ContentUrlRewriter::stripDimensionSuffix($normalized))] = $media;
    }

    private function resolveRelativePath(?string $attachedFile, string $guid): ?string
    {
        if (is_string($attachedFile) && trim($attachedFile) !== '') {
            return ltrim(str_replace('\\', '/', trim($attachedFile)), '/');
        }

        if (preg_match('#/wp-content/uploads/([^\s?\#]+)#i', $guid, $match)) {
            return ltrim(rawurldecode($match[1]), '/');
        }

        return null;
    }

    private function findSourceFile(string $uploadsRoot, string $relativePath): ?string
    {
        $normalized = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
        $candidate = $uploadsRoot.DIRECTORY_SEPARATOR.$normalized;

        return is_file($candidate) ? $candidate : null;
    }

    private function resolveUploadsRoot(string $sourcePath, ?string &$tempExtractDir): string
    {
        if (is_dir($sourcePath)) {
            $nestedUploads = rtrim($sourcePath, '\\/').DIRECTORY_SEPARATOR.'uploads';

            return is_dir($nestedUploads) ? $nestedUploads : rtrim($sourcePath, '\\/');
        }

        $tempExtractDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wp-uploads-'.Str::random(8);
        if (! mkdir($tempExtractDir, 0755, true) && ! is_dir($tempExtractDir)) {
            throw new RuntimeException("Unable to create temporary directory: {$tempExtractDir}");
        }

        $process = new Process(['tar', '-xzf', $sourcePath, '-C', $tempExtractDir]);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Failed to extract archive: '.$process->getErrorOutput());
        }

        $nestedUploads = $tempExtractDir.DIRECTORY_SEPARATOR.'uploads';

        return is_dir($nestedUploads) ? $nestedUploads : $tempExtractDir;
    }
}
