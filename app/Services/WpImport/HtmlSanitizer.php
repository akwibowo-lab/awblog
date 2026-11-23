<?php

namespace App\Services\WpImport;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizer
{
    /**
     * Allowed HTML tags and their permitted attributes.
     *
     * @var array<string, list<string>>
     */
    private const ALLOWED_TAGS = [
        'p' => [],
        'br' => [],
        'hr' => [],
        'h1' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'h5' => [],
        'h6' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        's' => [],
        'del' => [],
        'blockquote' => ['cite'],
        'pre' => ['class'],
        'code' => ['class'],
        'ul' => [],
        'ol' => ['start'],
        'li' => [],
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'figure' => [],
        'figcaption' => [],
        'table' => [],
        'thead' => [],
        'tbody' => [],
        'tfoot' => [],
        'tr' => [],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
    ];

    /**
     * Tags whose entire subtree (including text content) must be removed.
     *
     * @var list<string>
     */
    private const DROP_SUBTREE_TAGS = [
        'script',
        'style',
        'iframe',
        'object',
        'embed',
        'form',
        'input',
        'button',
        'textarea',
        'select',
        'svg',
    ];

    /**
     * Sanitize raw WordPress HTML content using an explicit tag/attribute allowlist.
     */
    public function sanitize(string $html): string
    {
        $cleaned = preg_replace('/<!--\s*\/?wp:[^>]*?-->/s', '', $html) ?? $html;
        $cleaned = trim($cleaned);

        if ($cleaned === '') {
            return '';
        }

        $previousErrors = libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $wrapped = '<div id="__wp_import_root__">'.$cleaned.'</div>';
        $dom->loadHTML(
            mb_encode_numericentity($wrapped, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $root = $dom->getElementById('__wp_import_root__');

        if (! $root instanceof DOMElement) {
            return '';
        }

        $this->sanitizeChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output);
    }

    private function sanitizeChildren(DOMNode $parent): void
    {
        // Snapshot child nodes because we mutate the DOM during iteration.
        $children = iterator_to_array($parent->childNodes);

        foreach ($children as $child) {
            if ($child->nodeType === XML_COMMENT_NODE) {
                $parent->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_SUBTREE_TAGS, true)) {
                $parent->removeChild($child);

                continue;
            }

            $this->sanitizeChildren($child);

            if (! array_key_exists($tag, self::ALLOWED_TAGS)) {
                while ($child->firstChild !== null) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);

                continue;
            }

            $this->sanitizeAttributes($child, $tag);
        }
    }

    private function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::ALLOWED_TAGS[$tag];
        $attributes = iterator_to_array($element->attributes);

        foreach ($attributes as $attr) {
            $name = strtolower($attr->nodeName);

            if (! in_array($name, $allowed, true)) {
                $element->removeAttributeNode($attr);

                continue;
            }

            if (in_array($name, ['href', 'src', 'cite'], true) && $this->isUnsafeUrl($attr->nodeValue ?? '')) {
                $element->removeAttributeNode($attr);
            }
        }

        if ($tag === 'img' && ! $element->hasAttribute('loading')) {
            $element->setAttribute('loading', 'lazy');
        }
    }

    private function isUnsafeUrl(string $url): bool
    {
        $normalized = strtolower(preg_replace('/[\x00-\x20]+/', '', $url) ?? $url);

        if ($normalized === '') {
            return true;
        }

        return str_starts_with($normalized, 'javascript:')
            || str_starts_with($normalized, 'vbscript:')
            || str_starts_with($normalized, 'data:');
    }
}
