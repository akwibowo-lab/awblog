<?php

namespace App\Services\WpImport;

use App\Models\Media;
use DOMDocument;
use DOMElement;

class ContentUrlRewriter
{
    /**
     * Extract relative upload paths (after /wp-content/uploads/) from HTML content.
     *
     * @return list<string>
     */
    public static function extractUploadPaths(string $html): array
    {
        if (! str_contains($html, 'wp-content/uploads/')) {
            return [];
        }

        preg_match_all(
            '#(?:https?://[^\s"\'<>]+)?/wp-content/uploads/([^\s"\'<>?\#]+)#i',
            $html,
            $matches
        );

        $paths = [];
        foreach ($matches[1] ?? [] as $rawPath) {
            $decoded = ltrim(rawurldecode($rawPath), '/');
            if ($decoded !== '') {
                $paths[] = $decoded;
            }
        }

        return array_values(array_unique($paths));
    }

    /**
     * Strip WordPress generated dimension suffix (e.g. -1024x682) before the extension.
     */
    public static function stripDimensionSuffix(string $relativePath): string
    {
        return preg_replace('/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', $relativePath) ?? $relativePath;
    }

    /**
     * Rewrite /wp-content/uploads/ URLs in post HTML to local /storage/media/ URLs and enforce loading="lazy".
     *
     * @param  array<string, Media>  $mediaByRelPath  Keyed by lowercase relative upload path (e.g. "2016/05/photo.jpg")
     * @return array{html: string, rewrites: int}
     */
    public function rewrite(string $html, array $mediaByRelPath): array
    {
        if (trim($html) === '') {
            return ['html' => $html, 'rewrites' => 0];
        }

        $previousErrors = libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $wrapped = '<div id="__wp_rewrite_root__">'.$html.'</div>';
        $dom->loadHTML(
            mb_encode_numericentity($wrapped, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $root = $dom->getElementById('__wp_rewrite_root__');
        if (! $root instanceof DOMElement) {
            return ['html' => $html, 'rewrites' => 0];
        }

        $rewrites = 0;

        foreach ($root->getElementsByTagName('img') as $img) {
            if (! $img instanceof DOMElement) {
                continue;
            }

            if (! $img->hasAttribute('loading') || $img->getAttribute('loading') === '') {
                $img->setAttribute('loading', 'lazy');
                $rewrites++;
            }

            $src = $img->getAttribute('src');
            if ($src !== '') {
                $replacement = $this->resolveReplacementUrl($src, $mediaByRelPath, preferWebpVariant: true);
                if ($replacement !== null && $replacement !== $src) {
                    $img->setAttribute('src', $replacement);
                    $rewrites++;
                }
            }
        }

        foreach ($root->getElementsByTagName('a') as $anchor) {
            if (! $anchor instanceof DOMElement) {
                continue;
            }

            $href = $anchor->getAttribute('href');
            if ($href !== '' && str_contains($href, 'wp-content/uploads/')) {
                $replacement = $this->resolveReplacementUrl($href, $mediaByRelPath, preferWebpVariant: false);
                if ($replacement !== null && $replacement !== $href) {
                    $anchor->setAttribute('href', $replacement);
                    $rewrites++;
                }
            }
        }

        if ($rewrites === 0) {
            return ['html' => $html, 'rewrites' => 0];
        }

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return ['html' => trim($output), 'rewrites' => $rewrites];
    }

    /**
     * @param  array<string, Media>  $mediaByRelPath
     */
    private function resolveReplacementUrl(string $url, array $mediaByRelPath, bool $preferWebpVariant): ?string
    {
        if (! preg_match('#(?:https?://[^\s"\'<>]+)?/wp-content/uploads/([^\s"\'<>?\#]+)#i', $url, $match)) {
            return null;
        }

        $relativePath = ltrim(rawurldecode($match[1]), '/');
        $candidates = [
            strtolower($relativePath),
            strtolower(self::stripDimensionSuffix($relativePath)),
        ];

        $media = null;
        foreach ($candidates as $candidate) {
            if (isset($mediaByRelPath[$candidate])) {
                $media = $mediaByRelPath[$candidate];
                break;
            }
        }

        if (! $media instanceof Media) {
            return null;
        }

        $targetPath = $media->path;

        if ($preferWebpVariant && is_array($media->variants)) {
            $targetPath = $media->variants['large']
                ?? $media->variants['medium']
                ?? $media->variants['thumbnail']
                ?? $media->path;
        }

        return '/storage/'.ltrim($targetPath, '/');
    }
}
