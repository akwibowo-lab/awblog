<?php

use App\Enums\ContentStatus;
use App\Enums\ProjectType;
use App\Http\Middleware\SetPublicLocale;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('language switcher is shown on home and project pages but hidden on blog', function () {
    Project::factory()->create([
        'slug' => 'demo-bilingual-project',
        'type' => ProjectType::App,
        'title_id' => 'Proyek Demo',
        'title_en' => 'Demo Project',
        'status' => ContentStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get('/')
        ->assertStatus(200)
        ->assertSee('id="lang-toggle"', false)
        ->assertSee('<html lang="id">', false);

    $this->get('/proyek')
        ->assertStatus(200)
        ->assertSee('id="lang-toggle"', false);

    $this->get('/proyek/demo-bilingual-project')
        ->assertStatus(200)
        ->assertSee('id="lang-toggle"', false);

    $this->get('/blog')
        ->assertStatus(200)
        ->assertDontSee('id="lang-toggle"', false)
        ->assertSee('<html lang="id">', false);
});

test('switching locale sets cookie and renders english on home and projects while blog stays indonesian', function () {
    Project::factory()->create([
        'slug' => 'mapping-app',
        'type' => ProjectType::App,
        'title_id' => 'Aplikasi Pemetaan',
        'title_en' => 'Mapping Application',
        'summary_id' => 'Ringkasan dalam bahasa Indonesia.',
        'summary_en' => 'Summary in English.',
        'description_id' => '<p>Deskripsi Indonesia.</p>',
        'description_en' => '<p>English description.</p>',
        'status' => ContentStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->from('/')
        ->get('/lang/en')
        ->assertRedirect('/')
        ->assertCookie(SetPublicLocale::COOKIE_NAME, 'en');

    $this->withCookie(SetPublicLocale::COOKIE_NAME, 'en')
        ->get('/')
        ->assertStatus(200)
        ->assertSee('<html lang="en">', false)
        ->assertSee('property="og:locale" content="en_US"', false)
        ->assertSee('Drone Certification &amp; Capabilities', false)
        ->assertSee('Mapping Application')
        ->assertSee('Summary in English.');

    $this->withCookie(SetPublicLocale::COOKIE_NAME, 'en')
        ->get('/proyek')
        ->assertStatus(200)
        ->assertSee('<html lang="en">', false)
        ->assertSee('Projects &amp; Apps', false)
        ->assertSee('Mapping Application');

    $this->withCookie(SetPublicLocale::COOKIE_NAME, 'en')
        ->get('/proyek/mapping-app')
        ->assertStatus(200)
        ->assertSee('<html lang="en">', false)
        ->assertSee('Tech Stack')
        ->assertSee('English description.', false);

    $this->withCookie(SetPublicLocale::COOKIE_NAME, 'en')
        ->get('/blog')
        ->assertStatus(200)
        ->assertDontSee('id="lang-toggle"', false)
        ->assertSee('<html lang="id">', false)
        ->assertSee('Catatan &amp; Tulisan', false);
});

test('auto-detects english for non-id cloudflare country header when no cookie is set and respects manual cookie', function () {
    $this->withHeaders(['CF-IPCountry' => 'US'])
        ->get('/')
        ->assertStatus(200)
        ->assertSee('<html lang="en">', false)
        ->assertSee('Drone Certification &amp; Capabilities', false);

    $this->withHeaders(['CF-IPCountry' => 'ID'])
        ->get('/')
        ->assertStatus(200)
        ->assertSee('<html lang="id">', false)
        ->assertSee('Sertifikasi &amp; Kapabilitas Drone', false);

    $this->withHeaders(['CF-IPCountry' => 'US'])
        ->withCookie(SetPublicLocale::COOKIE_NAME, 'id')
        ->get('/')
        ->assertStatus(200)
        ->assertSee('<html lang="id">', false);

    $this->withHeaders(['CF-IPCountry' => 'US'])
        ->get('/blog')
        ->assertStatus(200)
        ->assertSee('<html lang="id">', false);
});
