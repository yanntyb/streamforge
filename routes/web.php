<?php

use App\Http\Controllers\TikTokCallbackController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing.home')->name('home');
Route::view('terms', 'landing.terms')->name('terms');
Route::view('privacy', 'landing.privacy')->name('privacy');

Route::middleware(['auth'])->group(function () {
    Route::get('tiktok/callback', TikTokCallbackController::class)->name('tiktok.callback');
});
