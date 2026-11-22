<?php

use App\Enums\ContentStatus;
use App\Enums\ProjectType;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('all core content tables and key columns exist', function () {
    expect(Schema::hasColumns('media', [
        'id', 'wp_id', 'wp_source_url', 'disk', 'path', 'filename', 'mime_type', 'variants',
    ]))->toBeTrue();

    expect(Schema::hasColumns('categories', [
        'id', 'wp_term_id', 'parent_id', 'name', 'slug', 'description',
    ]))->toBeTrue();

    expect(Schema::hasColumns('tags', [
        'id', 'wp_term_id', 'name', 'slug', 'description',
    ]))->toBeTrue();

    expect(Schema::hasColumns('posts', [
        'id', 'wp_id', 'author_id', 'featured_media_id', 'title', 'slug',
        'excerpt', 'content_html', 'status', 'published_at',
        'meta_title', 'meta_description', 'canonical_url', 'reading_time_minutes', 'deleted_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('category_post', ['category_id', 'post_id']))->toBeTrue();
    expect(Schema::hasColumns('post_tag', ['post_id', 'tag_id']))->toBeTrue();

    expect(Schema::hasColumns('projects', [
        'id', 'featured_media_id', 'slug', 'type', 'title_id', 'title_en',
        'summary_id', 'summary_en', 'description_id', 'description_en',
        'live_url', 'repo_url', 'tech_stack', 'is_featured', 'sort_order',
        'status', 'published_at', 'deleted_at',
    ]))->toBeTrue();
});

test('post relations with user, media, categories, and tags work', function () {
    $author = User::factory()->create();
    $media = Media::factory()->create(['wp_id' => 101]);
    $category = Category::factory()->create(['wp_term_id' => 12]);
    $tag = Tag::factory()->create(['wp_term_id' => 45]);

    $post = Post::factory()->published()->create([
        'wp_id' => 999,
        'author_id' => $author->id,
        'featured_media_id' => $media->id,
    ]);

    $post->categories()->attach($category);
    $post->tags()->attach($tag);

    $freshPost = Post::with(['author', 'featuredMedia', 'categories', 'tags'])->findOrFail($post->id);

    expect($freshPost->author->is($author))->toBeTrue()
        ->and($freshPost->featuredMedia->is($media))->toBeTrue()
        ->and($freshPost->categories)->toHaveCount(1)
        ->and($freshPost->tags)->toHaveCount(1)
        ->and($author->posts)->toHaveCount(1)
        ->and($media->posts)->toHaveCount(1)
        ->and($category->posts)->toHaveCount(1)
        ->and($tag->posts)->toHaveCount(1);
});

test('category supports self-referencing parent and children hierarchy', function () {
    $parent = Category::factory()->create(['slug' => 'drone']);
    $child = Category::factory()->create(['parent_id' => $parent->id, 'slug' => 'fpv']);

    expect($child->parent->is($parent))->toBeTrue()
        ->and($parent->children)->toHaveCount(1)
        ->and($parent->children->first()->is($child))->toBeTrue();
});

test('project casts enums, json tech_stack, and links to media', function () {
    $media = Media::factory()->create();
    $project = Project::factory()->published()->featured()->create([
        'featured_media_id' => $media->id,
        'type' => ProjectType::Drone,
        'tech_stack' => ['DJI Mavic 3', 'APDI', 'Pix4D'],
    ]);

    $fresh = $project->fresh();

    expect($fresh->type)->toBe(ProjectType::Drone)
        ->and($fresh->status)->toBe(ContentStatus::Published)
        ->and($fresh->is_featured)->toBeTrue()
        ->and($fresh->tech_stack)->toBe(['DJI Mavic 3', 'APDI', 'Pix4D'])
        ->and($fresh->featuredMedia->is($media))->toBeTrue()
        ->and($media->projects)->toHaveCount(1);
});

test('scopes filter published posts and published, featured, ordered projects', function () {
    Post::factory()->create(['status' => ContentStatus::Draft]);
    Post::factory()->create(['status' => ContentStatus::Published, 'published_at' => now()->addDay()]);
    $publishedPost = Post::factory()->published()->create();

    expect(Post::published()->pluck('id')->all())->toBe([$publishedPost->id]);

    Project::factory()->create(['status' => ContentStatus::Draft, 'is_featured' => true]);
    $second = Project::factory()->published()->featured()->create(['sort_order' => 2]);
    $first = Project::factory()->published()->featured()->create(['sort_order' => 1]);
    Project::factory()->published()->create(['is_featured' => false, 'sort_order' => 0]);

    expect(Project::published()->featured()->ordered()->pluck('id')->all())
        ->toBe([$first->id, $second->id]);
});

test('unique constraints, nullOnDelete, and soft deletes behave as expected', function () {
    $media = Media::factory()->create(['wp_id' => 500]);
    $post = Post::factory()->create([
        'slug' => 'unik-slug',
        'wp_id' => 700,
        'featured_media_id' => $media->id,
    ]);

    expect(fn () => Post::factory()->create(['slug' => 'unik-slug']))
        ->toThrow(QueryException::class);

    expect(fn () => Post::factory()->create(['wp_id' => 700]))
        ->toThrow(QueryException::class);

    $media->delete();
    expect($post->fresh()->featured_media_id)->toBeNull();

    $post->delete();
    expect(Post::count())->toBe(0)
        ->and(Post::withTrashed()->count())->toBe(1);
});
