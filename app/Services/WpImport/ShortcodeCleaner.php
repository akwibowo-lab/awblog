<?php

namespace App\Services\WpImport;

class ShortcodeCleaner
{
    /**
     * Remove legacy WordPress plugin shortcodes while keeping inner content of wrapper tags.
     */
    public function clean(string $html): string
    {
        if ($html === '' || ! str_contains($html, '[')) {
            return $html;
        }

        // Strip opening/closing wrapper shortcode tags while preserving inner HTML/images.
        $wrapperTags = 'caption|one_half|one_half_last|alert-announce|alert-warning|button-red';
        $cleaned = (string) preg_replace('/\[\/?(?:'.$wrapperTags.')(?:\s+[^\]]*)?\]/i', '', $html);

        // Strip standalone plugin shortcodes completely.
        $standaloneTags = 'gallery|soundcloud|vc_video|vc_gmaps|vc_raw_js|contact-form-7|youtube|googlemap|NEW|TUTUP';
        $cleaned = (string) preg_replace('/\[(?:'.$standaloneTags.')(?:\s+[^\]]*)?\]/i', '', $cleaned);

        // Remove empty paragraphs left after stripping standalone shortcodes.
        $cleaned = (string) preg_replace('/<p>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/i', '', $cleaned);

        return trim($cleaned);
    }

    /**
     * Convert WordPress Classic Editor double-newline blocks into <p> tags without breaking existing block HTML.
     */
    public function formatParagraphs(string $html): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", trim($html));

        if ($normalized === '') {
            return '';
        }

        $preBlocks = [];
        $normalized = (string) preg_replace_callback('/<pre\b[^>]*>.*?<\/pre>/is', function (array $matches) use (&$preBlocks): string {
            $key = '<!--PRE_BLOCK_'.count($preBlocks).'-->';
            $preBlocks[$key] = $matches[0];

            return $key;
        }, $normalized);

        $blockPattern = '/^<(?:p|h[1-6]|ul|ol|blockquote|pre|table|figure|hr|div|section|article|aside|iframe|!--PRE_BLOCK_)\b/i';
        $chunks = preg_split('/\n{2,}/', $normalized) ?: [];
        $output = [];

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }

            if (preg_match($blockPattern, $chunk)) {
                $output[] = $chunk;
            } else {
                $withBreaks = (string) preg_replace('/\n/', "<br>\n", $chunk);
                $output[] = '<p>'.$withBreaks.'</p>';
            }
        }

        $result = implode("\n\n", $output);

        if ($preBlocks !== []) {
            $result = strtr($result, $preBlocks);
        }

        return $result;
    }
}
