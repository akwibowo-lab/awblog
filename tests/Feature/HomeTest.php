<?php

use App\Enums\ContentStatus;
use App\Enums\ProjectType;
use App\Models\Media;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('home page returns 200 with seo meta and all five portfolio sections', function () {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('Akut Wibowo')
        ->assertSee('fetchpriority="high"', false)
        ->assertSee('<meta name="description"', false)
        ->assertSee('<link rel="canonical"', false)
        ->assertSee('property="og:title"', false)
        ->assertSee('Sertifikasi &amp; Kapabilitas Drone', false)
        ->assertSee('Galeri Aplikasi &amp; Proyek', false)
        ->assertSee('Tulisan Terbaru', false)
        ->assertSee('Diskusi Proyek atau Kolaborasi', false)
        ->assertSee('https://wa.me/6289623084000')
        ->assertSee('mailto:akwibowo@gmail.com')
        ->assertSee('https://awork.id')
        ->assertSee('awork.id')
        ->assertSee('ODM Studio App');
});

test('home page renders published projects with lazy webp media and excludes drafts', function () {
    $media = Media::factory()->create([
        'path' => 'media/2026/11/project-demo.jpg',
        'variants' => ['medium' => 'media/2026/11/project-demo-medium.webp'],
        'width' => 1200,
        'height' => 800,
        'alt_text' => 'Tampilan aplikasi pemetaan',
    ]);

    Project::factory()->create([
        'featured_media_id' => $media->id,
        'type' => ProjectType::App,
        'title_id' => 'Proyek Pemetaan Aktif',
        'summary_id' => 'Aplikasi pemrosesan citra udara.',
        'tech_stack' => ['Laravel', 'Tailwind CSS'],
        'live_url' => 'https://example.test/live',
        'is_featured' => true,
        'status' => ContentStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    Project::factory()->create([
        'title_id' => 'Proyek Draft Rahasia',
        'status' => ContentStatus::Draft,
        'published_at' => null,
    ]);

    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('Proyek Pemetaan Aktif')
        ->assertSee('project-demo-medium.webp')
        ->assertSee('loading="lazy"', false)
        ->assertSee('https://example.test/live')
        ->assertSee('/proyek/')
        ->assertSee('Lihat Detail')
        ->assertDontSee('Proyek Draft Rahasia');
});

test('home page renders at most three latest published blog posts', function () {
    $postMedia = Media::factory()->create([
        'path' => 'media/2026/11/post-cover.jpg',
        'variants' => ['medium' => 'media/2026/11/post-cover-medium.webp'],
        'width' => 1200,
        'height' => 750,
    ]);

    foreach (range(1, 4) as $index) {
        Post::factory()->create([
            'featured_media_id' => $index === 4 ? $postMedia->id : null,
            'title' => "Artikel Terbaru {$index}",
            'slug' => "artikel-terbaru-{$index}",
            'status' => ContentStatus::Published,
            'published_at' => now()->subDays(5 - $index),
        ]);
    }

    Post::factory()->create([
        'title' => 'Artikel Draft Tersembunyi',
        'slug' => 'artikel-draft-tersembunyi',
        'status' => ContentStatus::Draft,
        'published_at' => null,
    ]);

    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('Artikel Terbaru 4')
        ->assertSee('Artikel Terbaru 3')
        ->assertSee('Artikel Terbaru 2')
        ->assertSee('post-cover-medium.webp')
        ->assertDontSee('Artikel Terbaru 1')
        ->assertDontSee('Artikel Draft Tersembunyi');
});
