<?php

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('blog index lists published posts only with seo meta and webp lazy images', function () {
    $category = Category::factory()->create([
        'name' => 'Drone Mapping',
        'slug' => 'drone-mapping',
    ]);

    $media = Media::factory()->create([
        'alt_text' => 'Pemetaan udara area pesisir',
        'variants' => [
            'thumbnail' => 'media/2026/11/coastal-thumb.webp',
            'medium' => 'media/2026/11/coastal-medium.webp',
            'large' => 'media/2026/11/coastal-large.webp',
        ],
    ]);

    $published = Post::factory()->published()->create([
        'title' => 'Panduan Pemetaan Drone RTK',
        'slug' => 'panduan-pemetaan-drone-rtk',
        'featured_media_id' => $media->id,
        'published_at' => now()->subDay(),
    ]);
    $published->categories()->attach($category);

    $olderMedia = Media::factory()->create([
        'alt_text' => 'Foto udara proyek kedua',
        'variants' => [
            'medium' => 'media/2026/11/second-medium.webp',
        ],
    ]);

    $older = Post::factory()->published()->create([
        'title' => 'Kalibrasi Kompas Sebelum Terbang',
        'slug' => 'kalibrasi-kompas-sebelum-terbang',
        'featured_media_id' => $olderMedia->id,
        'published_at' => now()->subDays(2),
    ]);
    $older->categories()->attach($category);

    Post::factory()->create([
        'title' => 'Draft Rahasia Tidak Terbit',
        'slug' => 'draft-rahasia',
        'status' => ContentStatus::Draft,
    ]);

    Post::factory()->create([
        'title' => 'Jadwal Masa Depan',
        'slug' => 'jadwal-masa-depan',
        'status' => ContentStatus::Published,
        'published_at' => now()->addDays(3),
    ]);

    $response = $this->get('/blog');

    $response->assertOk()
        ->assertSee('Catatan &amp; Tulisan', false)
        ->assertSee('Panduan Pemetaan Drone RTK')
        ->assertSee('Kalibrasi Kompas Sebelum Terbang')
        ->assertDontSee('Draft Rahasia Tidak Terbit')
        ->assertDontSee('Jadwal Masa Depan')
        ->assertSee('<link rel="canonical" href="'.route('blog.index').'">', false)
        ->assertSee('<meta property="og:type" content="website">', false)
        ->assertSee('coastal-large.webp')
        ->assertSee('fetchpriority="high"', false)
        ->assertSee('second-medium.webp')
        ->assertSee('loading="lazy"', false)
        ->assertSee('id="theme-toggle"', false)
        ->assertSee('alt="Pemetaan udara area pesisir"', false);
});

test('post detail preserves legacy root slug url with seo, og, hero image, and related posts', function () {
    $category = Category::factory()->create([
        'name' => 'Fotogrametri',
        'slug' => 'fotogrametri',
    ]);
    $tag = Tag::factory()->create([
        'name' => 'WebODM',
        'slug' => 'webodm',
    ]);

    $media = Media::factory()->create([
        'alt_text' => 'Hasil orthomosaic WebODM',
        'variants' => [
            'medium' => 'media/2026/11/odm-medium.webp',
            'large' => 'media/2026/11/odm-large.webp',
        ],
    ]);

    $post = Post::factory()->published()->create([
        'title' => 'Workflow Orthomosaic dengan WebODM',
        'slug' => 'workflow-orthomosaic-webodm',
        'featured_media_id' => $media->id,
        'content_html' => "Paragraf pertama catatan lapangan.\r\n\r\nParagraf kedua tentang kalibrasi.\r\n\r\n[caption id=\"attachment_3394\" align=\"aligncenter\" width=\"784\"]<img src=\"/storage/media/inline.webp\" alt=\"Contoh inline\">[/caption][gallery bgs_gallery_image_size=\"full\" bgs_gallery_title=\"Kegiatan\" ids=\"3397,3396\"]",
        'meta_title' => 'Workflow Orthomosaic WebODM',
        'meta_description' => 'Catatan praktis memproses foto udara dengan WebODM.',
    ]);
    $post->categories()->attach($category);
    $post->tags()->attach($tag);

    $related = Post::factory()->published()->create([
        'title' => 'Kalibrasi Kamera Drone untuk Pemetaan',
        'slug' => 'kalibrasi-kamera-drone',
    ]);
    $related->categories()->attach($category);

    $response = $this->get('/workflow-orthomosaic-webodm');

    $response->assertOk()
        ->assertSee('Workflow Orthomosaic dengan WebODM')
        ->assertSee('<title>Workflow Orthomosaic WebODM | Akut Wibowo</title>', false)
        ->assertSee('<meta name="description" content="Catatan praktis memproses foto udara dengan WebODM.">', false)
        ->assertSee('<link rel="canonical" href="'.route('blog.show', ['slug' => 'workflow-orthomosaic-webodm']).'">', false)
        ->assertSee('<meta property="og:type" content="article">', false)
        ->assertSee('odm-large.webp')
        ->assertSee('fetchpriority="high"', false)
        ->assertSee('<p>Paragraf pertama catatan lapangan.</p>', false)
        ->assertSee('<p>Paragraf kedua tentang kalibrasi.</p>', false)
        ->assertSee('<img decoding="async" loading="lazy" src="/storage/media/inline.webp" alt="Contoh inline">', false)
        ->assertDontSee('[caption')
        ->assertDontSee('[/caption]')
        ->assertDontSee('[gallery')
        ->assertSee('#WebODM')
        ->assertSee('Kalibrasi Kamera Drone untuk Pemetaan');
});

test('unpublished, future, and unknown post slugs return 404', function () {
    Post::factory()->create([
        'slug' => 'masih-draft',
        'status' => ContentStatus::Draft,
    ]);

    Post::factory()->create([
        'slug' => 'terbit-besok',
        'status' => ContentStatus::Published,
        'published_at' => now()->addDay(),
    ]);

    $this->get('/masih-draft')->assertNotFound();
    $this->get('/terbit-besok')->assertNotFound();
    $this->get('/tidak-pernah-ada')->assertNotFound();
});

test('category and tag archives filter published posts and preserve wordpress urls', function () {
    $droneCategory = Category::factory()->create(['name' => 'Drone', 'slug' => 'drone']);
    $devCategory = Category::factory()->create(['name' => 'Kode', 'slug' => 'kode']);
    $laravelTag = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

    $dronePost = Post::factory()->published()->create(['title' => 'Terbang Aman di Area Pesisir']);
    $dronePost->categories()->attach($droneCategory);

    $devPost = Post::factory()->published()->create(['title' => 'Membangun Blog dengan Laravel']);
    $devPost->categories()->attach($devCategory);
    $devPost->tags()->attach($laravelTag);

    $this->get('/category/drone')
        ->assertOk()
        ->assertSee('Kategori: Drone')
        ->assertSee('Terbang Aman di Area Pesisir')
        ->assertDontSee('Membangun Blog dengan Laravel')
        ->assertSee('<link rel="canonical" href="'.route('blog.category', ['category' => 'drone']).'">', false);

    $this->get('/tag/laravel')
        ->assertOk()
        ->assertSee('Tag: Laravel')
        ->assertSee('Membangun Blog dengan Laravel')
        ->assertDontSee('Terbang Aman di Area Pesisir')
        ->assertSee('<link rel="canonical" href="'.route('blog.tag', ['tag' => 'laravel']).'">', false);
});

test('blog search filters matching posts and displays clear empty state when nothing matches', function () {
    Post::factory()->published()->create([
        'title' => 'Checklist Pra-Terbang Drone',
        'excerpt' => 'Daftar periksa baterai, kompas, dan izin terbang.',
    ]);

    Post::factory()->published()->create([
        'title' => 'Catatan Setup Linux Server',
        'excerpt' => 'Konfigurasi SSH key dan firewall.',
    ]);

    $this->get('/blog?q=Pra-Terbang')
        ->assertOk()
        ->assertSee('Checklist Pra-Terbang Drone')
        ->assertDontSee('Catatan Setup Linux Server');

    $this->get('/blog?q=katakuncitidakada123')
        ->assertOk()
        ->assertSee('Tidak ada tulisan yang cocok')
        ->assertSee('Lihat semua tulisan');
});

test('blog index and post detail trigger zero lazy loading n+1 queries', function () {
    $category = Category::factory()->create();
    $tag = Tag::factory()->create();
    $media = Media::factory()->create();

    $posts = Post::factory()->count(5)->published()->create([
        'featured_media_id' => $media->id,
    ]);

    foreach ($posts as $post) {
        $post->categories()->attach($category);
        $post->tags()->attach($tag);
    }

    Model::preventLazyLoading(true);

    try {
        $this->get('/blog')->assertOk();
        $this->get('/category/'.$category->slug)->assertOk();
        $this->get('/tag/'.$tag->slug)->assertOk();
        $this->get('/'.$posts->first()->slug)->assertOk();
    } finally {
        Model::preventLazyLoading(false);
    }
});

test('wp:clean-content strips legacy shortcodes and removes broken images', function () {
    Http::fake([
        'https://example.com/ok.jpg' => Http::response('', 200, ['Content-Type' => 'image/jpeg']),
        'https://example.com/broken.jpg' => Http::response('', 404, ['Content-Type' => 'text/html']),
    ]);

    $post = Post::factory()->published()->create([
        'content_html' => '<p>Halo</p>[caption id="1"]<img src="https://example.com/ok.jpg" alt="OK">[/caption]<p><img src="https://example.com/broken.jpg" alt="Broken"></p>[gallery ids="1,2"]',
    ]);

    $this->artisan('wp:clean-content')
        ->assertSuccessful();

    $post->refresh();

    expect($post->content_html)
        ->toContain('https://example.com/ok.jpg')
        ->not->toContain('https://example.com/broken.jpg')
        ->not->toContain('[caption')
        ->not->toContain('[gallery');
});
