<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\WpImport\ShortcodeCleaner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CleanBlogContentCommand extends Command
{
    protected $signature = 'wp:clean-content {--dry-run : Preview changes without updating the database}';

    protected $description = 'Strip legacy WordPress plugin shortcodes and remove broken <img> tags from post content';

    /**
     * @var array<string, bool>
     */
    private array $externalUrlCache = [];

    public function handle(ShortcodeCleaner $shortcodeCleaner): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updatedPosts = 0;
        $removedImages = 0;

        Post::query()->orderBy('id')->chunkById(50, function ($posts) use ($shortcodeCleaner, $dryRun, &$updatedPosts, &$removedImages): void {
            foreach ($posts as $post) {
                $original = (string) $post->content_html;
                if ($original === '') {
                    continue;
                }

                $cleaned = $shortcodeCleaner->clean($original);
                $postRemovedImgs = 0;

                if (str_contains($cleaned, '<img')) {
                    $cleaned = (string) preg_replace_callback('/<img\b[^>]*>/i', function (array $matches) use (&$postRemovedImgs): string {
                        $tag = $matches[0];
                        if (! preg_match('/\bsrc\s*=\s*["\']([^"\']+)["\']/i', $tag, $srcMatch)) {
                            $postRemovedImgs++;

                            return '';
                        }

                        if ($this->isImageUrlValid(trim($srcMatch[1]))) {
                            return $tag;
                        }

                        $postRemovedImgs++;

                        return '';
                    }, $cleaned);

                    $cleaned = (string) preg_replace('/<figure\b[^>]*>\s*(?:<figcaption\b[^>]*>.*?<\/figcaption>\s*)?<\/figure>/is', '', $cleaned);
                    $cleaned = (string) preg_replace('/<p>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/i', '', $cleaned);
                    $cleaned = trim($cleaned);
                }

                if ($cleaned !== $original) {
                    $updatedPosts++;
                    $removedImages += $postRemovedImgs;

                    if (! $dryRun) {
                        $post->forceFill(['content_html' => $cleaned])->save();
                    }
                }
            }
        });

        $prefix = $dryRun ? '[DRY-RUN] ' : '';
        $this->info("{$prefix}Posts updated: {$updatedPosts}, broken images removed: {$removedImages}.");

        return self::SUCCESS;
    }

    private function isImageUrlValid(string $src): bool
    {
        if ($src === '') {
            return false;
        }

        if (str_starts_with($src, '/storage/')) {
            $rel = substr($src, strlen('/storage/'));
            $path = storage_path('app/public/'.$rel);

            if (! is_file($path) || filesize($path) === 0) {
                return false;
            }

            if (str_ends_with(strtolower($path), '.svg')) {
                return true;
            }

            return @getimagesize($path) !== false;
        }

        if (! preg_match('/^https?:\/\//i', $src)) {
            return false;
        }

        if (array_key_exists($src, $this->externalUrlCache)) {
            return $this->externalUrlCache[$src];
        }

        try {
            $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->timeout(6)
                ->head($src);

            $contentType = strtolower((string) $response->header('Content-Type'));
            $valid = $response->successful() && str_starts_with($contentType, 'image/');
        } catch (\Throwable) {
            $valid = false;
        }

        return $this->externalUrlCache[$src] = $valid;
    }
}
