<?php

use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/tag/{tag:slug}', [BlogController::class, 'tag'])->name('blog.tag');

// Preserve legacy WordPress /%postname%/ article URLs while keeping reserved prefixes free.
Route::get('/{slug}', [BlogController::class, 'show'])
    ->where('slug', '^(?!admin$|up$|blog$|category$|tag$|storage$|livewire$)[A-Za-z0-9\-]+$')
    ->name('blog.show');
