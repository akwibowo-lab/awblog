<?php

namespace App\Services\WpImport;

use GdImage;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MediaProcessor
{
    /**
     * Variant target maximum widths in pixels.
     *
     * @var array<string, int>
     */
    public const VARIANT_WIDTHS = [
        'thumbnail' => 300,
        'medium' => 768,
        'large' => 1200,
    ];

    private const WEBP_QUALITY = 82;

    /**
     * Store the original file on the target disk and generate WebP size variants for raster images.
     *
     * @return array{
     *     path: string,
     *     filename: string,
     *     mime_type: string,
     *     size_bytes: int,
     *     width: int|null,
     *     height: int|null,
     *     variants: array<string, string>
     * }
     */
    public function process(string $sourceFilePath, string $relativePath, string $disk = 'public'): array
    {
        $normalizedRel = ltrim(str_replace('\\', '/', $relativePath), '/');
        $storagePath = 'media/'.$normalizedRel;
        $storage = Storage::disk($disk);

        $contents = file_get_contents($sourceFilePath);
        if ($contents === false) {
            throw new \RuntimeException("Unable to read source media file: {$sourceFilePath}");
        }

        $storage->put($storagePath, $contents);

        $sizeBytes = filesize($sourceFilePath) ?: strlen($contents);
        $mimeType = $this->detectMimeType($sourceFilePath);
        $filename = pathinfo($normalizedRel, PATHINFO_BASENAME);

        [$width, $height, $variants] = $this->generateWebpVariants($sourceFilePath, $normalizedRel, $mimeType, $disk);

        return [
            'path' => $storagePath,
            'filename' => $filename,
            'mime_type' => $mimeType,
            'size_bytes' => $sizeBytes,
            'width' => $width,
            'height' => $height,
            'variants' => $variants,
        ];
    }

    /**
     * @return array{0: int|null, 1: int|null, 2: array<string, string>}
     */
    private function generateWebpVariants(string $sourceFilePath, string $normalizedRel, string $mimeType, string $disk): array
    {
        if (! function_exists('imagewebp')) {
            return [null, null, []];
        }

        $imageInfo = @getimagesize($sourceFilePath);
        if ($imageInfo === false) {
            return [null, null, []];
        }

        $sourceImage = $this->createGdImage($sourceFilePath, $mimeType);
        if (! $sourceImage instanceof GdImage) {
            return [$imageInfo[0] ?? null, $imageInfo[1] ?? null, []];
        }

        if ($mimeType === 'image/jpeg') {
            $sourceImage = $this->applyExifOrientation($sourceImage, $sourceFilePath);
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            imagedestroy($sourceImage);

            return [null, null, []];
        }

        $dir = pathinfo($normalizedRel, PATHINFO_DIRNAME);
        $baseName = pathinfo($normalizedRel, PATHINFO_FILENAME);
        $prefix = ($dir !== '' && $dir !== '.') ? "media/{$dir}/{$baseName}" : "media/{$baseName}";

        $storage = Storage::disk($disk);
        $variants = [];

        foreach (self::VARIANT_WIDTHS as $name => $maxWidth) {
            $targetWidth = min($sourceWidth, $maxWidth);
            $targetHeight = max(1, (int) round($sourceHeight * ($targetWidth / $sourceWidth)));

            $canvas = $this->resizeImage($sourceImage, $sourceWidth, $sourceHeight, $targetWidth, $targetHeight);
            if (! $canvas instanceof GdImage) {
                continue;
            }

            $webpBinary = $this->encodeWebp($canvas);
            imagedestroy($canvas);

            if ($webpBinary === null) {
                continue;
            }

            $variantPath = "{$prefix}-{$name}.webp";
            $storage->put($variantPath, $webpBinary);
            $variants[$name] = $variantPath;
        }

        imagedestroy($sourceImage);

        return [$sourceWidth, $sourceHeight, $variants];
    }

    private function createGdImage(string $filePath, string $mimeType): ?GdImage
    {
        try {
            $image = match ($mimeType) {
                'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($filePath),
                'image/png' => @imagecreatefrompng($filePath),
                'image/gif' => @imagecreatefromgif($filePath),
                'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($filePath) : false,
                default => false,
            };
        } catch (Throwable) {
            return null;
        }

        if (! $image instanceof GdImage) {
            return null;
        }

        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        return $image;
    }

    private function applyExifOrientation(GdImage $image, string $filePath): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($filePath);
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated instanceof GdImage) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }

    private function resizeImage(
        GdImage $source,
        int $srcWidth,
        int $srcHeight,
        int $dstWidth,
        int $dstHeight
    ): ?GdImage {
        $canvas = imagecreatetruecolor($dstWidth, $dstHeight);
        if (! $canvas instanceof GdImage) {
            return null;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        if ($transparent !== false) {
            imagefilledrectangle($canvas, 0, 0, $dstWidth, $dstHeight, $transparent);
        }

        $ok = imagecopyresampled($canvas, $source, 0, 0, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight);
        if (! $ok) {
            imagedestroy($canvas);

            return null;
        }

        return $canvas;
    }

    private function encodeWebp(GdImage $image): ?string
    {
        ob_start();
        $ok = @imagewebp($image, null, self::WEBP_QUALITY);
        $binary = ob_get_clean();

        if (! $ok || ! is_string($binary) || $binary === '') {
            return null;
        }

        return $binary;
    }

    private function detectMimeType(string $filePath): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            if (is_string($detected) && $detected !== '') {
                return $detected;
            }
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'zip' => 'application/zip',
            default => 'application/octet-stream',
        };
    }
}
