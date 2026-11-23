<?php

namespace App\Services\Seo;

use Illuminate\Support\Str;

class PostSeoAnalyzer
{
    /**
     * Evaluate SEO readiness of a post based on its focus keyword, metadata, and content.
     *
     * @return array{
     *     score: int,
     *     label: string,
     *     tone: 'success'|'warning'|'danger',
     *     word_count: int,
     *     checks: list<array{status: 'pass'|'warn'|'fail', message: string}>
     * }
     */
    public function analyze(
        ?string $focusKeyword,
        ?string $title,
        ?string $slug,
        ?string $metaTitle,
        ?string $metaDescription,
        ?string $contentHtml,
        bool $hasFeaturedImage = false,
        ?string $featuredImageAlt = null,
    ): array {
        $keyword = mb_strtolower(trim((string) $focusKeyword));
        $effectiveTitle = trim((string) $metaTitle) !== '' ? trim((string) $metaTitle) : trim((string) $title);
        $effectiveDescription = trim((string) $metaDescription);
        $plainContent = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $contentHtml))) ?? '');
        $wordCount = $plainContent === '' ? 0 : count(preg_split('/\s+/u', $plainContent, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        $checks = [];
        $points = 0;
        $maxPoints = 0;

        $addCheck = static function (string $status, string $message, int $weight) use (&$checks, &$points, &$maxPoints): void {
            $maxPoints += $weight;
            if ($status === 'pass') {
                $points += $weight;
            } elseif ($status === 'warn') {
                $points += (int) round($weight * 0.5);
            }
            $checks[] = ['status' => $status, 'message' => $message];
        };

        // 1. Meta title length (30–60 chars)
        $titleLen = mb_strlen($effectiveTitle);
        if ($titleLen >= 30 && $titleLen <= 60) {
            $addCheck('pass', "Panjang SEO title ideal ({$titleLen} karakter, target 30–60).", 15);
        } elseif ($titleLen > 0) {
            $addCheck('warn', "Panjang SEO title {$titleLen} karakter (disarankan 30–60 karakter).", 15);
        } else {
            $addCheck('fail', 'Judul / Meta Title masih kosong.', 15);
        }

        // 2. Meta description length (120–160 chars)
        $descLen = mb_strlen($effectiveDescription);
        if ($descLen >= 120 && $descLen <= 160) {
            $addCheck('pass', "Panjang Meta Description ideal ({$descLen} karakter, target 120–160).", 15);
        } elseif ($descLen > 0) {
            $addCheck('warn', "Meta Description {$descLen} karakter (disarankan 120–160 karakter).", 15);
        } else {
            $addCheck('fail', 'Meta Description belum diisi.', 15);
        }

        // 3. Content length (>= 300 words)
        if ($wordCount >= 300) {
            $addCheck('pass', "Panjang artikel cukup ({$wordCount} kata).", 10);
        } elseif ($wordCount >= 120) {
            $addCheck('warn', "Panjang artikel {$wordCount} kata (disarankan minimal 300 kata).", 10);
        } else {
            $addCheck('fail', "Artikel terlalu pendek ({$wordCount} kata, target minimal 300 kata).", 10);
        }

        // 4. Featured image / inline image + alt text SEO
        $hasInlineImage = (bool) preg_match('/<img\b/i', (string) $contentHtml);
        $featuredAlt = trim((string) $featuredImageAlt);
        $inlineAlts = [];
        if ($hasInlineImage && preg_match_all('/<img\b[^>]*\balt=["\']([^"\']*)["\']/i', (string) $contentHtml, $matches)) {
            $inlineAlts = array_filter(array_map('trim', $matches[1]));
        }

        if (! $hasFeaturedImage && ! $hasInlineImage) {
            $addCheck('fail', 'Belum ada gambar utama atau gambar di dalam artikel.', 10);
        } elseif ($featuredAlt === '' && $inlineAlts === []) {
            $addCheck('warn', 'Gambar ada, tetapi alt text masih kosong (isi Alt Text di Media untuk SEO).', 10);
        } elseif ($keyword !== '') {
            $allAltsLower = mb_strtolower(trim($featuredAlt.' '.implode(' ', $inlineAlts)));
            if (str_contains($allAltsLower, $keyword)) {
                $addCheck('pass', 'Gambar memiliki alt text yang mengandung focus keyword.', 10);
            } else {
                $addCheck('warn', 'Alt text gambar terisi, tetapi belum mengandung focus keyword.', 10);
            }
        } else {
            $addCheck('pass', 'Gambar memiliki alt text.', 10);
        }

        // 5–8. Keyword-specific checks
        if ($keyword === '') {
            $addCheck('fail', 'Focus Keyword belum diisi — isi untuk analisis penempatan keyword.', 50);
        } else {
            $lowerTitle = mb_strtolower($effectiveTitle);
            if (str_contains($lowerTitle, $keyword)) {
                $addCheck('pass', 'Focus keyword muncul di SEO Title.', 15);
            } else {
                $addCheck('fail', 'Focus keyword belum muncul di Judul / Meta Title.', 15);
            }

            $keywordSlug = Str::slug($keyword);
            if ($keywordSlug !== '' && str_contains(mb_strtolower((string) $slug), $keywordSlug)) {
                $addCheck('pass', 'Focus keyword muncul di URL slug.', 10);
            } else {
                $addCheck('warn', 'Focus keyword belum muncul di URL slug.', 10);
            }

            if ($effectiveDescription !== '' && str_contains(mb_strtolower($effectiveDescription), $keyword)) {
                $addCheck('pass', 'Focus keyword muncul di Meta Description.', 10);
            } else {
                $addCheck('fail', 'Focus keyword belum muncul di Meta Description.', 10);
            }

            $lowerContent = mb_strtolower($plainContent);
            $occurrences = $lowerContent !== '' ? substr_count($lowerContent, $keyword) : 0;
            $keywordWordCount = max(1, count(preg_split('/\s+/u', $keyword, -1, PREG_SPLIT_NO_EMPTY) ?: []));
            $density = $wordCount > 0 ? ($occurrences * $keywordWordCount / $wordCount) * 100 : 0.0;

            if ($occurrences >= 1 && $density >= 0.4 && $density <= 3.0) {
                $densityFormatted = number_format($density, 1);
                $addCheck('pass', "Focus keyword muncul {$occurrences}x di konten (kepadatan {$densityFormatted}%).", 15);
            } elseif ($occurrences >= 1) {
                $densityFormatted = number_format($density, 1);
                $addCheck('warn', "Focus keyword muncul {$occurrences}x (kepadatan {$densityFormatted}%, ideal 0.5%–2.5%).", 15);
            } else {
                $addCheck('fail', 'Focus keyword belum muncul di isi artikel.', 15);
            }
        }

        $score = $maxPoints > 0 ? (int) round(($points / $maxPoints) * 100) : 0;

        [$label, $tone] = match (true) {
            $score >= 80 => ['Terbaik', 'success'],
            $score >= 50 => ['Cukup', 'warning'],
            default => ['Kurang', 'danger'],
        };

        return [
            'score' => $score,
            'label' => $label,
            'tone' => $tone,
            'word_count' => $wordCount,
            'checks' => $checks,
        ];
    }
}
