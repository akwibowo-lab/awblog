<?php

use App\Enums\ContentStatus;
use App\Enums\ProjectType;
use App\Models\Project;
use App\Services\Translation\GeminiProjectTranslator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('project renders typed links with web, github, playstore, and download icons', function () {
    Project::factory()->create([
        'slug' => 'multi-link-app',
        'type' => ProjectType::App,
        'title_id' => 'Aplikasi Multi Tautan',
        'summary_id' => 'Ringkasan aplikasi.',
        'live_url' => 'https://awork.id',
        'repo_url' => 'https://github.com/akwibowo-lab/awblog',
        'links' => [
            ['type' => 'web', 'url' => 'https://awork.id', 'label' => 'Web Resmi'],
            ['type' => 'playstore', 'url' => 'https://play.google.com/store/apps/details?id=id.awork', 'label' => 'Play Store'],
            ['type' => 'download', 'url' => 'https://example.test/manual.pdf', 'label' => 'Unduh Manual'],
        ],
        'status' => ContentStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get('/')
        ->assertStatus(200)
        ->assertSee('Web Resmi')
        ->assertSee('Play Store')
        ->assertSee('Unduh Manual')
        ->assertSee('GitHub');

    $this->get('/proyek/multi-link-app')
        ->assertStatus(200)
        ->assertSee('Web Resmi')
        ->assertSee('Play Store')
        ->assertSee('Unduh Manual')
        ->assertSee('GitHub');
});

test('gemini project translator returns english draft fields and validates api key', function () {
    config()->set('services.gemini.api_key', '');

    expect(fn () => app(GeminiProjectTranslator::class)->translateToEnglish('Judul', 'Ringkasan', '<p>Isi</p>'))
        ->toThrow(RuntimeException::class, 'GEMINI_API_KEY');

    config()->set('services.gemini.api_key', 'test-gemini-key');
    config()->set('services.gemini.model', 'gemini-2.5-flash');

    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    'title_en' => 'Coastal Mapping',
                                    'summary_en' => 'Aerial photogrammetry corridor mapping.',
                                    'description_en' => '<p>High-resolution orthomosaic output.</p>',
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $result = app(GeminiProjectTranslator::class)->translateToEnglish(
        'Pemetaan Pesisir',
        'Pemetaan fotogrametri koridor udara.',
        '<p>Keluaran ortomosaik resolusi tinggi.</p>'
    );

    expect($result)->toBe([
        'title_en' => 'Coastal Mapping',
        'summary_en' => 'Aerial photogrammetry corridor mapping.',
        'description_en' => '<p>High-resolution orthomosaic output.</p>',
    ]);
});

test('gemini project translator falls back to next model when primary returns high demand', function () {
    config()->set('services.gemini.api_key', 'test-gemini-key');
    config()->set('services.gemini.model', 'gemini-3.8-flash');

    Http::fake([
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent' => Http::response([
            'error' => ['message' => 'This model is currently experiencing high demand.'],
        ], 503),
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => json_encode([
                                    'title_en' => 'Fallback Success',
                                    'summary_en' => 'Translated via fallback.',
                                    'description_en' => '<p>Done.</p>',
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
        ], 200),
    ]);

    $result = app(GeminiProjectTranslator::class)->translateToEnglish('Sukses Fallback', 'Diterjemahkan via fallback.', '<p>Selesai.</p>');

    expect($result['title_en'])->toBe('Fallback Success');
});
