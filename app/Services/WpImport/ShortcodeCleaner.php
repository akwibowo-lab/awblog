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
}
