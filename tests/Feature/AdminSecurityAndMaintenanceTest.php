<?php

use App\Filament\Pages\ManageSiteSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PragmaRX\Google2FAQRCode\Google2FA;

uses(RefreshDatabase::class);

it('disables web password reset routes and removes filament branding traces in admin', function () {
    $this->get('/admin/password-reset/request')->assertNotFound();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('AW Admin')
        ->assertDontSee('filamentphp.com')
        ->assertDontSee('Filament v');
});

it('supports TOTP app authentication on user model and challenges 2FA on admin login', function () {
    $google2fa = app(Google2FA::class);
    $secret = $google2fa->generateSecretKey();

    $user = User::factory()->create([
        'password' => 'secret-pass-123',
        'app_authentication_secret' => $secret,
        'app_authentication_recovery_codes' => ['code-one', 'code-two'],
    ]);

    expect(app(AppAuthentication::class)->isEnabled($user))->toBeTrue();
    expect($user->getAppAuthenticationHolderName())->toBe($user->email);

    $component = Livewire::test(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'secret-pass-123',
        ])
        ->call('authenticate');

    $component->assertHasNoFormErrors();
    $this->assertGuest();
});

it('toggles frontend and backend maintenance mode via Pengaturan Umum and supports bypass and CLI reset', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(ManageSiteSettings::class)
        ->fillForm([
            'frontend_enabled' => true,
            'backend_enabled' => false,
            'title' => 'Sedang Pemeliharaan Terjadwal',
            'message' => 'Silakan kembali 15 menit lagi.',
            'bypass_token' => 'rahasia-aw-2026',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->get('/')->assertStatus(503)->assertSee('Sedang Pemeliharaan Terjadwal');
    $this->get('/blog')->assertStatus(503);
    $this->get('/?bypass=rahasia-aw-2026')->assertOk();

    // Backend dan endpoint Livewire ber-hash tetap bisa diakses saat hanya frontend maintenance aktif
    $this->actingAs($user)->get('/admin')->assertOk();
    expect($this->post(route('default-livewire.update'))->status())->not->toBe(503);

    // Aktifkan backend maintenance mode
    SiteSetting::saveMaintenance([
        'frontend_enabled' => true,
        'backend_enabled' => true,
        'title' => 'Admin & Situs Maintenance',
        'message' => 'Pemeliharaan penuh.',
        'bypass_token' => 'rahasia-aw-2026',
    ]);

    // Sesi admin yang sudah login tetap bisa mengakses /admin untuk mematikan maintenance
    $this->actingAs($user)->get('/admin')->assertOk();

    // Tamu yang belum login ke /admin melihat 503 kecuali memakai token bypass
    auth()->logout();
    $this->get('/admin/login')->assertStatus(503)->assertSee('Admin &amp; Situs Maintenance', false);
    $this->get('/admin/login?bypass=rahasia-aw-2026')->assertOk();

    // CLI safety command mematikan kedua mode
    $this->artisan('maintenance:off')->assertSuccessful();
    $this->get('/')->assertOk();
    $this->get('/admin/login')->assertOk();
});
