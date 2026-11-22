<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/feed', [SeoController::class, 'feed'])->name('seo.feed');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/tag/{tag:slug}', [BlogController::class, 'tag'])->name('blog.tag');

// Preserve legacy WordPress /%postname%/ article URLs while keeping reserved prefixes free.
Route::get('/{slug}', [BlogController::class, 'show'])
    ->where('slug', '^(?!admin$|up$|blog$|category$|tag$|storage$|livewire$|feed$)[A-Za-z0-9\-]+$')
    ->name('blog.show');
