<?php

use App\Http\Controllers\Admin\PreviewController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SeoController;
use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/lang/{locale}', [LocaleController::class, 'switch'])
    ->whereIn('locale', ['id', 'en'])
    ->name('locale.switch');

Route::middleware(FilamentAuthenticate::class)->prefix('admin/preview')->name('admin.preview.')->group(function (): void {
    Route::get('/posts/{post}', [PreviewController::class, 'post'])->name('post');
    Route::get('/projects/{project}', [PreviewController::class, 'project'])->name('project');
});

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/feed', [SeoController::class, 'feed'])->name('seo.feed');

Route::get('/proyek', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/proyek/{slug}', [ProjectController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('projects.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/tag/{tag:slug}', [BlogController::class, 'tag'])->name('blog.tag');

// Preserve legacy WordPress /%postname%/ article URLs while keeping reserved prefixes free.
Route::get('/{slug}', [BlogController::class, 'show'])
    ->where('slug', '^(?!admin$|up$|lang$|proyek$|blog$|category$|tag$|storage$|livewire$|feed$)[A-Za-z0-9\-]+$')
    ->name('blog.show');
