<?php

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('sitemap.xml returns valid xml with published posts, active categories, and active tags only', function () {
    $activeCategory = Category::factory()->create(['name' => 'Drone', 'slug' => 'drone']);
    $emptyCategory = Category::factory()->create(['name' => 'Kosong', 'slug' => 'kosong']);

    $activeTag = Tag::factory()->create(['name' => 'RTK', 'slug' => 'rtk']);
    $emptyTag = Tag::factory()->create(['name' => 'Tanpa Post', 'slug' => 'tanpa-post']);

    $published = Post::factory()->published()->create([
        'title' => 'Artikel Terbit Sitemap',
        'slug' => 'artikel-terbit-sitemap',
        'published_at' => now()->subDay(),
    ]);
    $published->categories()->attach($activeCategory);
    $published->tags()->attach($activeTag);

    Post::factory()->create([
        'title' => 'Artikel Draft',
        'slug' => 'artikel-draft-sitemap',
        'status' => ContentStatus::Draft,
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/xml');

    $xml = simplexml_load_string($response->getContent());
    expect($xml)->not->toBeFalse();

    $response->assertSee(route('home'), false)
        ->assertSee(route('blog.index'), false)
        ->assertSee(route('blog.show', 'artikel-terbit-sitemap'), false)
        ->assertSee(route('blog.category', 'drone'), false)
        ->assertSee(route('blog.tag', 'rtk'), false)
        ->assertDontSee('artikel-draft-sitemap', false)
        ->assertDontSee('/category/kosong', false)
        ->assertDontSee('/tag/tanpa-post', false);
});

test('rss feed returns valid rss 2.0 with latest 20 published posts and head alternate link', function () {
    $author = User::factory()->create(['name' => 'Akut Wibowo']);
    $category = Category::factory()->create(['name' => 'Pemetaan', 'slug' => 'pemetaan']);

    $posts = Post::factory()->count(22)->published()->create([
        'author_id' => $author->id,
        'published_at' => now()->subDays(2),
    ]);

    foreach ($posts as $index => $post) {
        $post->update([
            'title' => sprintf('Post Feed %02d', $index + 1),
            'slug' => sprintf('post-feed-%02d', $index + 1),
            'published_at' => now()->subHours(25 - $index),
        ]);
        $post->categories()->attach($category);
    }

    Post::factory()->create([
        'title' => 'Draft Rahasia Feed',
        'slug' => 'draft-rahasia-feed',
        'status' => ContentStatus::Draft,
    ]);

    $response = $this->get('/feed');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/rss+xml');

    $xml = simplexml_load_string($response->getContent());
    expect($xml)->not->toBeFalse();
    expect(count($xml->channel->item))->toBe(20);

    $response->assertSee('Post Feed 22', false)
        ->assertSee('Post Feed 03', false)
        ->assertDontSee('Post Feed 01', false)
        ->assertDontSee('Draft Rahasia Feed', false)
        ->assertSee('<dc:creator>Akut Wibowo</dc:creator>', false)
        ->assertSee('<category>Pemetaan</category>', false);

    $this->get('/blog')
        ->assertOk()
        ->assertSee('<link rel="alternate" type="application/rss+xml"', false)
        ->assertSee(route('seo.feed'), false);
});

test('database redirect rules issue 301 and track hit count while ignoring inactive or self-loop rules', function () {
    Redirect::query()->create([
        'source_path' => '/halaman-lama',
        'target_url' => '/blog',
        'status_code' => 301,
        'is_active' => true,
    ]);

    Redirect::query()->create([
        'source_path' => '/nonaktif',
        'target_url' => '/blog',
        'status_code' => 301,
        'is_active' => false,
    ]);

    Redirect::query()->create([
        'source_path' => '/loop-check',
        'target_url' => '/loop-check',
        'status_code' => 301,
        'is_active' => true,
    ]);

    $this->get('/halaman-lama/')
        ->assertStatus(301)
        ->assertRedirect('/blog');

    $rule = Redirect::query()->where('source_path', '/halaman-lama')->firstOrFail();
    expect($rule->hits)->toBe(1);
    expect($rule->last_hit_at)->not->toBeNull();

    $this->get('/nonaktif')->assertNotFound();
    $this->get('/loop-check')->assertNotFound();
});

test('legacy wordpress url patterns redirect 301 to canonical laravel routes', function () {
    $post = Post::factory()->published()->create([
        'wp_id' => 9876,
        'slug' => 'panduan-drone-rtk',
        'published_at' => now()->subDay(),
    ]);

    $category = Category::factory()->create(['slug' => 'drone']);

    $this->get('/?p=9876')
        ->assertStatus(301)
        ->assertRedirect($post->publicUrl());

    $this->get('/2024/05/12/panduan-drone-rtk/')
        ->assertStatus(301)
        ->assertRedirect($post->publicUrl());

    $this->get('/rss')
        ->assertStatus(301)
        ->assertRedirect(route('seo.feed'));

    $this->get('/feed/rss2')
        ->assertStatus(301)
        ->assertRedirect(route('seo.feed'));

    $this->get('/category/'.$category->slug.'/feed')
        ->assertStatus(301)
        ->assertRedirect(route('blog.category', $category->slug));

    $this->get('/author/akut')
        ->assertStatus(301)
        ->assertRedirect(route('blog.index'));
});
