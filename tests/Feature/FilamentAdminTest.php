<?php

use App\Filament\Pages\ManageHomepageSettings;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Filament\Resources\Media\Pages\CreateMedia;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Filament\Resources\Redirects\Pages\ManageRedirects;
use App\Filament\Resources\Tags\Pages\ManageTags;
use App\Models\Media;
use App\Models\Post;
use App\Models\Project;
use App\Models\Redirect;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Seo\PostSeoAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function makeTestPngUploadedFile(string $name = 'sample.png', int $width = 800, int $height = 600): UploadedFile
{
    $image = imagecreatetruecolor($width, $height);
    $color = imagecolorallocate($image, 245, 158, 11);
    imagefill($image, 0, 0, $color);

    $tmpPath = tempnam(sys_get_temp_dir(), 'awblog_img_').'.png';
    imagepng($image, $tmpPath);
    imagedestroy($image);

    return new UploadedFile($tmpPath, $name, 'image/png', null, true);
}

it('redirects guests from /admin to /admin/login and allows authenticated users', function () {
    $this->get('/admin')->assertRedirect('/admin/login');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('AW Admin');
});

it('renders all filament resource index pages and homepage settings page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(ListPosts::class)->assertOk();
    Livewire::test(ManageCategories::class)->assertOk();
    Livewire::test(ManageTags::class)->assertOk();
    Livewire::test(ListMedia::class)->assertOk();
    Livewire::test(ListProjects::class)->assertOk();
    Livewire::test(ManageRedirects::class)->assertOk();
    Livewire::test(ManageHomepageSettings::class)->assertOk();
});

it('creates a post, redirect, and media with webp variants from filament pages', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Catatan Uji Drone',
            'slug' => 'catatan-uji-drone',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'author_id' => $user->id,
            'excerpt' => 'Ringkasan uji.',
            'content_html' => '<p>Konten artikel uji.</p>',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()->where('slug', 'catatan-uji-drone')->exists())->toBeTrue();

    Livewire::test(ManageRedirects::class)
        ->callAction('create', data: [
            'source_path' => 'lama/artikel',
            'target_url' => '/catatan-uji-drone',
            'status_code' => 301,
            'is_active' => true,
        ])
        ->assertHasNoActionErrors();

    expect(Redirect::query()->where('source_path', '/lama/artikel')->exists())->toBeTrue();

    $png = makeTestPngUploadedFile('aerial.png');
    $storedTempPath = $png->store('media-uploads', 'public');

    Livewire::test(CreateMedia::class)
        ->fillForm([
            'uploaded_file' => [$storedTempPath],
            'alt_text' => 'Foto udara uji',
            'caption' => 'Dokumentasi lapangan',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $media = Media::query()->where('alt_text', 'Foto udara uji')->first();
    expect($media)->not->toBeNull();
    expect($media->variants)->toHaveKeys(['thumbnail', 'medium', 'large']);
    Storage::disk('public')->assertExists($media->path);
    Storage::disk('public')->assertExists($media->variants['medium']);
});

it('creates a project with uploaded thumbnail and links featured_media_id', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $this->actingAs($user);

    $png = makeTestPngUploadedFile('project-thumb.png');
    $storedTempPath = $png->storeAs('media-uploads', 'project-thumb.png', 'public');

    Livewire::test(CreateProject::class)
        ->fillForm([
            'title_id' => 'Proyek Pemetaan Baru',
            'title_en' => 'New Mapping Project',
            'slug' => 'proyek-pemetaan-baru',
            'type' => 'drone',
            'status' => 'published',
            'is_featured' => true,
            'sort_order' => 1,
            'thumbnail_upload' => [$storedTempPath],
            'summary_id' => 'Ringkasan proyek.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $project = Project::query()->where('slug', 'proyek-pemetaan-baru')->with('featuredMedia')->first();
    expect($project)->not->toBeNull();
    expect($project->published_at)->not->toBeNull();
    expect($project->featured_media_id)->not->toBeNull();
    expect($project->featuredMedia)->not->toBeNull();
    expect($project->featuredMedia->filename)->toBe('project-thumb.png');
    Storage::disk('public')->assertExists($project->featuredMedia->path);

    $this->get('/proyek')
        ->assertOk()
        ->assertSee('Proyek Pemetaan Baru');
});

it('updates homepage hero content, custom hero photo, and portfolio pdf via ManageHomepageSettings', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $this->actingAs($user);

    $heroPath = makeTestPngUploadedFile('hero-profile.png', 400, 400)->store('homepage', 'public');
    $pdfFile = UploadedFile::fake()->create('akut-wibowo-portfolio.pdf', 120, 'application/pdf');
    $pdfPath = $pdfFile->storeAs('portfolio', 'akut-wibowo-portfolio.pdf', 'public');

    Livewire::test(ManageHomepageSettings::class)
        ->fillForm([
            'hero_name' => 'Akut Wibowo Studio',
            'hero_role' => 'Pilot Drone & Sistem Web',
            'hero_image_path' => [$heroPath],
            'hero_image_alt' => 'Foto profil Akut Wibowo',
            'portfolio_pdf_path' => [$pdfPath],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = SiteSetting::getHomepage();
    expect($settings['hero_name'])->toBe('Akut Wibowo Studio');
    expect($settings['hero_image_path'])->toBe($heroPath);

    $this->get('/')
        ->assertOk()
        ->assertSee('Akut Wibowo Studio')
        ->assertSee('Pilot Drone &amp; Sistem Web', false)
        ->assertSee(asset('storage/'.$heroPath), false)
        ->assertSee(asset('storage/'.$pdfPath), false);
});

it('computes seo score based on focus keyword, metadata, and content length', function () {
    $analyzer = app(PostSeoAnalyzer::class);

    $weak = $analyzer->analyze(
        focusKeyword: null,
        title: 'Pendek',
        slug: 'pendek',
        metaTitle: null,
        metaDescription: null,
        contentHtml: '<p>Teks singkat.</p>',
        hasFeaturedImage: false,
    );

    expect($weak['score'])->toBeLessThan(50);
    expect($weak['tone'])->toBe('danger');

    $body = '<p>'.str_repeat('Operasi pilot drone bersertifikat untuk pemetaan udara membutuhkan ketelitian. ', 40).'</p>';
    $strong = $analyzer->analyze(
        focusKeyword: 'pilot drone bersertifikat',
        title: 'Panduan Lengkap Pilot Drone Bersertifikat di Indonesia',
        slug: 'panduan-pilot-drone-bersertifikat',
        metaTitle: 'Panduan Pilot Drone Bersertifikat untuk Pemetaan',
        metaDescription: 'Pelajari standar operasional pilot drone bersertifikat untuk pemetaan udara, keselamatan misi, dan dokumentasi lapangan secara menyeluruh.',
        contentHtml: $body,
        hasFeaturedImage: true,
        featuredImageAlt: 'Operasi pilot drone bersertifikat di lapangan',
    );

    expect($strong['score'])->toBeGreaterThanOrEqual(80);
    expect($strong['tone'])->toBe('success');
    expect($strong['label'])->toBe('Terbaik');
});

it('allows authenticated admin to preview draft posts and projects on dedicated preview routes while public urls return 404', function () {
    $user = User::factory()->create();

    $draftPost = Post::factory()->create([
        'title' => 'Draf Rahasia',
        'slug' => 'draf-rahasia',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $draftProject = Project::factory()->create([
        'title_id' => 'Proyek Draf',
        'slug' => 'proyek-draf',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $this->get('/draf-rahasia')->assertNotFound();
    $this->get('/proyek/proyek-draf')->assertNotFound();
    $this->get(route('admin.preview.post', $draftPost))->assertRedirect('/admin/login');
    $this->get(route('admin.preview.project', $draftProject))->assertRedirect('/admin/login');

    $this->actingAs($user)
        ->get(route('admin.preview.post', $draftPost))
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('Draf Rahasia');

    $this->actingAs($user)
        ->get(route('admin.preview.project', $draftProject))
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('Proyek Draf');
});

it('auto-saves draft posts and keeps at most two post revisions with restore support', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $post = Post::factory()->create([
        'author_id' => $user->id,
        'title' => 'Judul Awal',
        'slug' => 'judul-awal',
        'status' => 'draft',
        'published_at' => null,
        'content_html' => '<p>Versi 1</p>',
    ]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['title' => 'Judul AutoSave'])
        ->call('autoSaveDraft')
        ->assertHasNoFormErrors();

    expect($post->fresh()->title)->toBe('Judul AutoSave');
    expect($post->revisions()->count())->toBe(0);

    foreach (['Judul Revisi 1', 'Judul Revisi 2', 'Judul Revisi 3'] as $nextTitle) {
        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->fillForm(['title' => $nextTitle])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    $revisions = $post->revisions()->get();
    expect($revisions)->toHaveCount(2);
    expect($revisions->pluck('title')->all())->toBe(['Judul Revisi 2', 'Judul Revisi 1']);

    $oldestKept = $revisions->last();
    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->call('restoreRevision', $oldestKept->id);

    expect($post->fresh()->title)->toBe('Judul Revisi 1');
});
