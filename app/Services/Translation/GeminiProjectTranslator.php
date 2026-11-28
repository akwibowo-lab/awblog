<?php

namespace App\Services\Translation;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiProjectTranslator
{
    private const TIMEOUT_SECONDS = 45;

    /**
     * @var list<string>
     */
    private const FALLBACK_MODELS = [
        'gemini-3.5-flash-lite',
        'gemini-flash-latest',
        'gemini-3.8-flash',
    ];

    /**
     * Translate Indonesian project fields to English using Gemini API with fallback models.
     *
     * @return array{title_en: string, summary_en: string, description_en: string}
     */
    public function translateToEnglish(string $titleId, string $summaryId = '', string $descriptionId = ''): array
    {
        $apiKey = trim((string) config('services.gemini.api_key'));
        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY belum diatur di file .env.');
        }

        $configuredModel = trim((string) config('services.gemini.model', 'gemini-3.5-flash-lite'));
        $models = array_values(array_unique(array_filter([
            $configuredModel,
            ...self::FALLBACK_MODELS,
        ])));

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

        $lastError = 'Gagal menghubungi Gemini API.';

        foreach ($models as $model) {
            $endpoint = sprintf(
                'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
                rawurlencode($model)
            );

            try {
                $response = Http::timeout(self::TIMEOUT_SECONDS)
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
            } catch (ConnectionException $exception) {
                $lastError = $exception->getMessage();

                continue;
            }

            if ($response->failed()) {
                $status = $response->status();
                $lastError = (string) ($response->json('error.message') ?? $lastError);

                // Retry with next fallback model on transient capacity/rate/timeout or deprecated model errors.
                if (in_array($status, [404, 429, 500, 502, 503, 504], true)) {
                    continue;
                }

                throw new RuntimeException($lastError);
            }

            $rawText = (string) ($response->json('candidates.0.content.parts.0.text') ?? '');
            $decoded = json_decode(trim($rawText), true);

            if (! is_array($decoded)) {
                $lastError = 'Respons Gemini API tidak berformat JSON yang valid.';

                continue;
            }

            return [
                'title_en' => trim((string) ($decoded['title_en'] ?? '')),
                'summary_en' => trim((string) ($decoded['summary_en'] ?? '')),
                'description_en' => trim((string) ($decoded['description_en'] ?? '')),
            ];
        }

        throw new RuntimeException($lastError);
    }
}
