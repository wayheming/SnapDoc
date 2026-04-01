<?php
// app/routes/web.php

use App\Http\Controllers\PhotoController;
use App\Livewire\PhotoProcessor;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::get('/preview/{uuid}', [PhotoController::class, 'preview'])->name('preview');
Route::get('/download/{uuid}', [PhotoController::class, 'download'])->name('download');
Route::view('/privacy-policy', 'pages.privacy-policy')->name('privacy-policy');
