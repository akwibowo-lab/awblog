<?php

use App\Actions\Blog\ListPublishedPosts;
use App\Actions\Projects\ListPublishedProjects;
use App\Enums\ContentStatus;
use App\Enums\ProjectType;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Cache::flush();
});

it('caches homepage html response and invalidates when posts or projects change', function (): void {
    $author = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create([
        'title' => 'Artikel Cache Awal',
        'status' => ContentStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    $project = Project::factory()->create([
        'title_id' => 'Proyek Cache Awal',
        'status' => ContentStatus::Published,
        'published_at' => now()->subDay(),
        'sort_order' => 1,
    ]);

    $this->get('/')->assertOk()
        ->assertSee($post->title)
        ->assertSee($project->title_id);

    DB::enableQueryLog();
    DB::flushQueryLog();
    $this->get('/')->assertOk()
        ->assertSee($post->title)
        ->assertSee($project->title_id);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    $contentQueries = collect($queries)
        ->pluck('query')
        ->filter(fn (string $sql): bool => str_contains($sql, '"posts"') || str_contains($sql, '"projects"') || str_contains($sql, '"site_settings"'))
        ->all();

    expect($contentQueries)->toBeEmpty();

    $newPost = Post::factory()->for($author, 'author')->create([
        'title' => 'Artikel Cache Baru',
        'status' => ContentStatus::Published,
        'published_at' => now(),
    ]);
    $newProject = Project::factory()->create([
        'title_id' => 'Proyek Cache Baru',
        'status' => ContentStatus::Published,
        'published_at' => now(),
        'sort_order' => 2,
    ]);

    $this->get('/')->assertOk()
        ->assertSee($newPost->title)
        ->assertSee($newProject->title_id);
});

it('caches blog counts, categories, and popular tags and invalidates on post save', function (): void {
    $author = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Drone', 'slug' => 'drone']);
    $tag = Tag::factory()->create(['name' => 'FPV', 'slug' => 'fpv']);

    $post = Post::factory()->for($author, 'author')->create([
        'status' => ContentStatus::Published,
        'published_at' => now()->subHour(),
    ]);
    $post->categories()->attach($category);
    $post->tags()->attach($tag);

    $action = app(ListPublishedPosts::class);

    expect($action->publishedCount())->toBe(1)
        ->and($action->categoriesWithPublishedCount())->toHaveCount(1)
        ->and($action->popularTags(16))->toHaveCount(1);

    DB::enableQueryLog();
    DB::flushQueryLog();
    expect($action->publishedCount())->toBe(1)
        ->and($action->categoriesWithPublishedCount())->toHaveCount(1)
        ->and($action->popularTags(16))->toHaveCount(1);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    $contentQueries = collect($queries)
        ->pluck('query')
        ->filter(fn (string $sql): bool => str_contains($sql, '"posts"') || str_contains($sql, '"projects"') || str_contains($sql, '"site_settings"'))
        ->all();

    expect($contentQueries)->toBeEmpty();

    Post::factory()->for($author, 'author')->create([
        'status' => ContentStatus::Published,
        'published_at' => now(),
    ]);

    expect($action->publishedCount())->toBe(2);
});

it('caches published projects and filters by type without extra queries', function (): void {
    Project::factory()->create([
        'slug' => 'drone-mapping',
        'type' => ProjectType::Drone,
        'status' => ContentStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    Project::factory()->create([
        'slug' => 'web-app',
        'type' => ProjectType::App,
        'status' => ContentStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $action = app(ListPublishedProjects::class);
    expect($action->execute())->toHaveCount(2);

    DB::enableQueryLog();
    DB::flushQueryLog();
    $droneOnly = $action->execute(ProjectType::Drone);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($queries)->toBeEmpty()
        ->and($droneOnly)->toHaveCount(1)
        ->and($droneOnly->first()['slug'])->toBe('drone-mapping');
});

it('renders lcp hero attributes and webp lazy card images on homepage', function (): void {
    $author = User::factory()->create();
    $media = Media::factory()->create([
        'disk' => 'public',
        'path' => 'media/2026/11/sample.jpg',
        'variants' => [
            'thumbnail' => 'media/2026/11/sample-thumbnail.webp',
            'medium' => 'media/2026/11/sample-medium.webp',
            'large' => 'media/2026/11/sample-large.webp',
        ],
    ]);

    Post::factory()->for($author, 'author')->create([
        'title' => 'Uji Performa WebP',
        'slug' => 'uji-performa-webp',
        'status' => ContentStatus::Published,
        'published_at' => now()->subMinute(),
        'featured_media_id' => $media->id,
    ]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('fetchpriority="high"', false)
        ->assertSee('sample-medium.webp', false)
        ->assertSee('loading="lazy"', false);
});
