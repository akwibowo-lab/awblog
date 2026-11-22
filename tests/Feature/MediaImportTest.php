<?php

use App\Enums\ContentStatus;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Services\WpImport\MediaImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function setupMediaLegacyFixture(): string
{
    config([
        'database.connections.wp_legacy_media_test' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => 'c71gfp8_',
        ],
    ]);

    DB::purge('wp_legacy_media_test');

    $schema = Schema::connection('wp_legacy_media_test');

    $schema->create('posts', function (Blueprint $table) {
        $table->unsignedBigInteger('ID')->primary();
        $table->string('guid')->default('');
        $table->text('post_title')->nullable();
        $table->text('post_excerpt')->nullable();
        $table->string('post_mime_type', 100)->default('');
        $table->string('post_type', 20);
    });

    $schema->create('postmeta', function (Blueprint $table) {
        $table->unsignedBigInteger('meta_id')->primary();
        $table->unsignedBigInteger('post_id');
        $table->string('meta_key')->nullable();
        $table->longText('meta_value')->nullable();
    });

    $wp = DB::connection('wp_legacy_media_test');

    $wp->table('posts')->insert([
        [
            'ID' => 501,
            'guid' => 'https://akutwibowo.com/wp-content/uploads/2020/05/drone-view.jpg',
            'post_title' => 'Drone View',
            'post_excerpt' => 'Aerial photo caption',
            'post_mime_type' => 'image/jpeg',
            'post_type' => 'attachment',
        ],
    ]);

    $wp->table('postmeta')->insert([
        ['meta_id' => 1, 'post_id' => 501, 'meta_key' => '_wp_attached_file', 'meta_value' => '2020/05/drone-view.jpg'],
        ['meta_id' => 2, 'post_id' => 501, 'meta_key' => '_wp_attachment_image_alt', 'meta_value' => 'Drone aerial shot'],
        ['meta_id' => 3, 'post_id' => 101, 'meta_key' => '_thumbnail_id', 'meta_value' => '501'],
    ]);

    $tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'wp-media-fixture-'.uniqid();
    $uploadSubDir = $tempDir.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'2020'.DIRECTORY_SEPARATOR.'05';
    mkdir($uploadSubDir, 0755, true);

    $image = imagecreatetruecolor(1600, 900);
    $bg = imagecolorallocate($image, 12, 34, 56);
    imagefilledrectangle($image, 0, 0, 1599, 899, $bg);
    imagejpeg($image, $uploadSubDir.DIRECTORY_SEPARATOR.'drone-view.jpg', 90);
    imagedestroy($image);

    return $tempDir;
}

function createSamplePostForMedia(): Post
{
    $user = User::factory()->create();

    return Post::query()->create([
        'wp_id' => 101,
        'author_id' => $user->id,
        'title' => 'Pemetaan Drone Batam',
        'slug' => 'pemetaan-drone-batam',
        'content_html' => '<p>Lihat foto: <a href="http://akutwibowo.com/wp-content/uploads/2020/05/drone-view.jpg"><img src="https://akutwibowo.com/wp-content/uploads/2020/05/drone-view-1024x576.jpg" alt="Drone"></a></p>',
        'status' => ContentStatus::Published,
        'published_at' => now(),
    ]);
}

it('previews media import counts in dry-run mode without writing files', function () {
    Storage::fake('public');
    $fixtureDir = setupMediaLegacyFixture();
    $post = createSamplePostForMedia();

    try {
        $result = app(MediaImporter::class)->import($fixtureDir, 'wp_legacy_media_test', dryRun: true);

        expect($result->dryRun)->toBeTrue()
            ->and($result->mediaImported)->toBe(1)
            ->and($result->featuredLinked)->toBe(1)
            ->and($result->postsUpdated)->toBe(1)
            ->and(Media::count())->toBe(0);

        $post->refresh();
        expect($post->featured_media_id)->toBeNull()
            ->and($post->content_html)->toContain('wp-content/uploads/');
    } finally {
        File::deleteDirectory($fixtureDir);
    }
});

it('imports media, generates webp variants, links featured image, and rewrites post urls idempotently', function () {
    Storage::fake('public');
    $fixtureDir = setupMediaLegacyFixture();
    $post = createSamplePostForMedia();

    try {
        $importer = app(MediaImporter::class);
        $first = $importer->import($fixtureDir, 'wp_legacy_media_test', dryRun: false);

        expect($first->dryRun)->toBeFalse()
            ->and($first->mediaImported)->toBe(1)
            ->and($first->featuredLinked)->toBe(1)
            ->and($first->postsUpdated)->toBe(1)
            ->and($first->urlsRewritten)->toBeGreaterThanOrEqual(2);

        $media = Media::query()->where('wp_id', 501)->firstOrFail();

        expect($media->path)->toBe('media/2020/05/drone-view.jpg')
            ->and($media->width)->toBe(1600)
            ->and($media->height)->toBe(900)
            ->and($media->alt_text)->toBe('Drone aerial shot')
            ->and($media->caption)->toBe('Aerial photo caption')
            ->and($media->variants)->toHaveKeys(['thumbnail', 'medium', 'large']);

        Storage::disk('public')->assertExists($media->path);
        Storage::disk('public')->assertExists($media->variants['thumbnail']);
        Storage::disk('public')->assertExists($media->variants['medium']);
        Storage::disk('public')->assertExists($media->variants['large']);

        $post->refresh();
        expect($post->featured_media_id)->toBe($media->id)
            ->and($post->content_html)->not->toContain('wp-content/uploads/')
            ->and($post->content_html)->toContain('/storage/media/2020/05/drone-view-large.webp')
            ->and($post->content_html)->toContain('/storage/media/2020/05/drone-view.jpg')
            ->and($post->content_html)->toContain('loading="lazy"');

        // Second run must be idempotent.
        $second = $importer->import($fixtureDir, 'wp_legacy_media_test', dryRun: false);

        expect($second->mediaImported)->toBe(0)
            ->and($second->mediaSkipped)->toBe(1)
            ->and($second->featuredLinked)->toBe(0)
            ->and($second->postsUpdated)->toBe(0)
            ->and(Media::count())->toBe(1);
    } finally {
        File::deleteDirectory($fixtureDir);
    }
});
