<?php

use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/preview/{uuid}', [PhotoController::class, 'preview'])->name('preview');
Route::get('/download/{uuid}', [PhotoController::class, 'download'])->name('download');
Route::get('/privacy-policy', fn () => view('pages.privacy-policy'))->name('privacy-policy');
