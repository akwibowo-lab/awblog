<?php

use App\Enums\ContentStatus;
use App\Enums\ProjectType;
use App\Models\Media;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('projects index returns 200 with seo meta, navbar link, and fallback projects', function () {
    $response = $this->get('/proyek');

    $response->assertStatus(200)
        ->assertSee('Proyek &amp; Aplikasi', false)
        ->assertSee('<meta name="description"', false)
        ->assertSee('<link rel="canonical"', false)
        ->assertSee('property="og:title"', false)
        ->assertSee('href="'.route('projects.index').'"', false)
        ->assertSee('awork.id')
        ->assertSee('ODM Studio App');
});

test('projects index filters fallback items by type and shows empty state when none match', function () {
    $droneResponse = $this->get('/proyek?type=drone');

    $droneResponse->assertStatus(200)
        ->assertSee('ODM Studio App')
        ->assertDontSee('/proyek/awork-id');

    $emptyResponse = $this->get('/proyek?type=case_study');

    $emptyResponse->assertStatus(200)
        ->assertSee('Belum ada proyek pada kategori ini.')
        ->assertSee('Tampilkan semua proyek');
});

test('project detail renders fallback project pages and returns 404 for unknown slug', function () {
    $this->get('/proyek/awork-id')
        ->assertStatus(200)
        ->assertSee('awork.id')
        ->assertSee('https://awork.id');

    $this->get('/proyek/odm-studio-app')
        ->assertStatus(200)
        ->assertSee('ODM Studio App')
        ->assertSee('OpenDroneMap');

    $this->get('/proyek/tidak-ada')
        ->assertStatus(404);
});

test('projects pages render published database projects with media and exclude drafts', function () {
    $media = Media::factory()->create([
        'path' => 'media/2026/11/proyek-uji.jpg',
        'variants' => [
            'medium' => 'media/2026/11/proyek-uji-medium.webp',
            'large' => 'media/2026/11/proyek-uji-large.webp',
        ],
        'width' => 1200,
        'height' => 800,
        'alt_text' => 'Tangkapan layar proyek uji',
    ]);

    Project::factory()->create([
        'featured_media_id' => $media->id,
        'slug' => 'sistem-survei-udara',
        'type' => ProjectType::Drone,
        'title_id' => 'Sistem Survei Udara',
        'summary_id' => 'Ringkasan sistem survei udara terukur.',
        'description_id' => '<p>Detail lengkap proyek survei udara.</p>',
        'tech_stack' => ['Laravel', 'QGIS'],
        'live_url' => 'https://example.test/survei',
        'is_featured' => true,
        'status' => ContentStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    Project::factory()->create([
        'slug' => 'proyek-rahasia',
        'title_id' => 'Proyek Rahasia Draft',
        'status' => ContentStatus::Draft,
        'published_at' => null,
    ]);

    $indexResponse = $this->get('/proyek');
    $indexResponse->assertStatus(200)
        ->assertSee('Sistem Survei Udara')
        ->assertSee('proyek-uji-medium.webp')
        ->assertSee('loading="lazy"', false)
        ->assertDontSee('Proyek Rahasia Draft');

    $this->get('/proyek?type=app')
        ->assertStatus(200)
        ->assertSee('Belum ada proyek pada kategori ini.');

    $this->get('/proyek/sistem-survei-udara')
        ->assertStatus(200)
        ->assertSee('Sistem Survei Udara')
        ->assertSee('Detail lengkap proyek survei udara.')
        ->assertSee('proyek-uji-large.webp');

    $this->get('/proyek/proyek-rahasia')
        ->assertStatus(404);
});
