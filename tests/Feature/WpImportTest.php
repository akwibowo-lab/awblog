<?php

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\WpImport\HtmlSanitizer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function setupWpLegacyFixture(): void
{
    config([
        'database.connections.wp_legacy_test' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => 'c71gfp8_',
        ],
    ]);

    DB::purge('wp_legacy_test');

    $schema = Schema::connection('wp_legacy_test');

    $schema->create('users', function (Blueprint $table) {
        $table->unsignedBigInteger('ID')->primary();
        $table->string('user_login');
        $table->string('user_email');
        $table->string('display_name');
    });

    $schema->create('terms', function (Blueprint $table) {
        $table->unsignedBigInteger('term_id')->primary();
        $table->string('name');
        $table->string('slug');
    });

    $schema->create('term_taxonomy', function (Blueprint $table) {
        $table->unsignedBigInteger('term_taxonomy_id')->primary();
        $table->unsignedBigInteger('term_id');
        $table->string('taxonomy');
        $table->text('description');
        $table->unsignedBigInteger('parent')->default(0);
    });

    $schema->create('term_relationships', function (Blueprint $table) {
        $table->unsignedBigInteger('object_id');
        $table->unsignedBigInteger('term_taxonomy_id');
    });

    $schema->create('posts', function (Blueprint $table) {
        $table->unsignedBigInteger('ID')->primary();
        $table->unsignedBigInteger('post_author')->default(1);
        $table->string('post_date')->nullable();
        $table->string('post_date_gmt')->nullable();
        $table->longText('post_content');
        $table->text('post_title');
        $table->text('post_excerpt');
        $table->string('post_status', 20);
        $table->string('post_name');
        $table->string('post_type', 20);
    });

    $schema->create('postmeta', function (Blueprint $table) {
        $table->unsignedBigInteger('meta_id')->primary();
        $table->unsignedBigInteger('post_id');
        $table->string('meta_key')->nullable();
        $table->longText('meta_value')->nullable();
    });

    $wp = DB::connection('wp_legacy_test');

    $wp->table('users')->insert([
        'ID' => 1,
        'user_login' => 'akut',
        'user_email' => 'akut@akutwibowo.com',
        'display_name' => 'Akut Wibowo',
    ]);

    $wp->table('terms')->insert([
        ['term_id' => 10, 'name' => 'Drone &amp; Pemetaan', 'slug' => 'drone-pemetaan'],
        ['term_id' => 11, 'name' => 'Fotogrametri', 'slug' => 'fotogrametri'],
        ['term_id' => 20, 'name' => 'DJI Mavic', 'slug' => 'dji-mavic'],
    ]);

    $wp->table('term_taxonomy')->insert([
        ['term_taxonomy_id' => 100, 'term_id' => 10, 'taxonomy' => 'category', 'description' => 'Artikel drone', 'parent' => 0],
        ['term_taxonomy_id' => 101, 'term_id' => 11, 'taxonomy' => 'category', 'description' => 'Subkategori pemetaan', 'parent' => 10],
        ['term_taxonomy_id' => 200, 'term_id' => 20, 'taxonomy' => 'post_tag', 'description' => '', 'parent' => 0],
    ]);

    $wp->table('posts')->insert([
        [
            'ID' => 101,
            'post_author' => 1,
            'post_date' => '2025-01-15 10:00:00',
            'post_date_gmt' => '2025-01-15 03:00:00',
            'post_content' => '<!-- wp:paragraph --><p onclick="alert(1)">Panduan <strong>drone</strong>.</p><!-- /wp:paragraph --><script>evil()</script><img src="https://example.com/a.jpg" alt="Foto">',
            'post_title' => 'Panduan Pemetaan &amp; Drone',
            'post_excerpt' => 'Ringkasan singkat.',
            'post_status' => 'publish',
            'post_name' => 'panduan-pemetaan-drone',
            'post_type' => 'post',
        ],
        [
            'ID' => 102,
            'post_author' => 1,
            'post_date' => '2025-02-01 08:00:00',
            'post_date_gmt' => '0000-00-00 00:00:00',
            'post_content' => '<p>Draf catatan lapangan.</p>',
            'post_title' => 'Catatan Lapangan',
            'post_excerpt' => '',
            'post_status' => 'draft',
            'post_name' => '',
            'post_type' => 'post',
        ],
        [
            'ID' => 103,
            'post_author' => 1,
            'post_date' => '2025-02-02 08:00:00',
            'post_date_gmt' => '2025-02-02 01:00:00',
            'post_content' => '<p>Revisi internal</p>',
            'post_title' => 'Revisi',
            'post_excerpt' => '',
            'post_status' => 'inherit',
            'post_name' => '103-revision-v1',
            'post_type' => 'revision',
        ],
    ]);

    $wp->table('term_relationships')->insert([
        ['object_id' => 101, 'term_taxonomy_id' => 100],
        ['object_id' => 101, 'term_taxonomy_id' => 101],
        ['object_id' => 101, 'term_taxonomy_id' => 200],
    ]);

    $wp->table('postmeta')->insert([
        ['meta_id' => 1, 'post_id' => 101, 'meta_key' => '_yoast_wpseo_title', 'meta_value' => 'SEO Title Panduan'],
        ['meta_id' => 2, 'post_id' => 101, 'meta_key' => '_yoast_wpseo_metadesc', 'meta_value' => 'Deskripsi SEO'],
    ]);
}

it('sanitizes unsafe html tags, attributes, and urls while enforcing lazy images', function () {
    $sanitizer = new HtmlSanitizer;

    $dirty = '<!-- wp:paragraph --><p onclick="hack()">Halo <a href="javascript:alert(1)">klik</a> <a href="https://akutwibowo.com">aman</a></p><script>alert(1)</script><img src="/wp-content/uploads/a.jpg" alt="Drone">';
    $clean = $sanitizer->sanitize($dirty);

    expect($clean)
        ->not->toContain('<script>')
        ->not->toContain('onclick')
        ->not->toContain('javascript:')
        ->not->toContain('wp:paragraph')
        ->toContain('href="https://akutwibowo.com"')
        ->toContain('loading="lazy"');
});

it('imports wordpress categories, tags, posts, seo meta, and relations idempotently', function () {
    setupWpLegacyFixture();

    $this->artisan('wp:import', ['--connection' => 'wp_legacy_test'])
        ->assertSuccessful();

    expect(User::count())->toBe(1)
        ->and(Category::count())->toBe(2)
        ->and(Tag::count())->toBe(1)
        ->and(Post::count())->toBe(2);

    $parent = Category::where('wp_term_id', 10)->firstOrFail();
    $child = Category::where('wp_term_id', 11)->firstOrFail();
    expect($parent->name)->toBe('Drone & Pemetaan')
        ->and($child->parent_id)->toBe($parent->id);

    $post = Post::where('wp_id', 101)->firstOrFail();
    expect($post->slug)->toBe('panduan-pemetaan-drone')
        ->and($post->title)->toBe('Panduan Pemetaan & Drone')
        ->and($post->status)->toBe(ContentStatus::Published)
        ->and($post->meta_title)->toBe('SEO Title Panduan')
        ->and($post->meta_description)->toBe('Deskripsi SEO')
        ->and($post->content_html)->not->toContain('<script>')
        ->and($post->content_html)->toContain('loading="lazy"')
        ->and($post->categories)->toHaveCount(2)
        ->and($post->tags)->toHaveCount(1);

    $draft = Post::where('wp_id', 102)->firstOrFail();
    expect($draft->status)->toBe(ContentStatus::Draft)
        ->and($draft->slug)->toBe('catatan-lapangan');

    // Re-running import must be idempotent.
    $this->artisan('wp:import', ['--connection' => 'wp_legacy_test'])
        ->assertSuccessful();

    expect(Post::count())->toBe(2)
        ->and(Category::count())->toBe(2)
        ->and(Tag::count())->toBe(1)
        ->and(User::count())->toBe(1);
});

it('supports dry-run without writing records', function () {
    setupWpLegacyFixture();

    $this->artisan('wp:import', [
        '--connection' => 'wp_legacy_test',
        '--dry-run' => true,
    ])->assertSuccessful();

    expect(Post::count())->toBe(0)
        ->and(Category::count())->toBe(0)
        ->and(Tag::count())->toBe(0)
        ->and(User::count())->toBe(0);
});
