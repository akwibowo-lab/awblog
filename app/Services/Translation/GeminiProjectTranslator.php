<?php

namespace App\Services\Translation;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProjectTranslator
{
    /**
     * Translate Indonesian project fields to English using Gemini API.
     *
     * @return array{title_en: string, summary_en: string, description_en: string}
     */
    public function translateToEnglish(string $titleId, string $summaryId = '', string $descriptionId = ''): array
    {
        $apiKey = trim((string) config('services.gemini.api_key'));
        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY belum diatur di file .env.');
        }

        $model = trim((string) config('services.gemini.model', 'gemini-2.5-flash'));
        if ($model === '') {
            $model = 'gemini-2.5-flash';
        }

        $payload = [
            'title_id' => trim($titleId),
            'summary_id' => trim($summaryId),
            'description_id' => trim($descriptionId),
        ];

        $prompt = <<<'PROMPT'
Translate the following Indonesian portfolio project fields into natural, professional English for a drone pilot and software builder portfolio.
Rules:
- Keep technical terms, brand names (like awork.id, OpenDroneMap, Laravel, QGIS), and proper nouns accurate.
- Preserve all HTML tags in description_id (e.g. <p>, <h2>, <ul>, <li>, <strong>, <a>) without adding markdown fences.
- If an input field is empty, return an empty string for that field.
- Return strictly a JSON object with keys: title_en, summary_en, description_en.

Input JSON:
PROMPT."\n".json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $endpoint = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            rawurlencode($model)
        );

        $response = Http::timeout(25)
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            $message = (string) ($response->json('error.message') ?? 'Gagal menghubungi Gemini API.');
            throw new RuntimeException($message);
        }

        $rawText = (string) ($response->json('candidates.0.content.parts.0.text') ?? '');
        $decoded = json_decode(trim($rawText), true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Respons Gemini API tidak berformat JSON yang valid.');
        }

        return [
            'title_en' => trim((string) ($decoded['title_en'] ?? '')),
            'summary_en' => trim((string) ($decoded['summary_en'] ?? '')),
            'description_en' => trim((string) ($decoded['description_en'] ?? '')),
        ];
    }
}
