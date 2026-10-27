<?php

use App\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Catalog & Information Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/katalog', [CatalogController::class, 'explore'])->name('catalog.index');
Route::get('/katalog/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

/*
|--------------------------------------------------------------------------
| Policy & Information Pages
|--------------------------------------------------------------------------
*/
Route::view('/tentang-kami', 'pages.about')->name('about');
Route::view('/bantuan', 'pages.faq')->name('faq');
Route::view('/syarat-ketentuan', 'pages.terms')->name('terms');
