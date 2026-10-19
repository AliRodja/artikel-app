<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleReportController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicArticleController;
use Illuminate\Support\Facades\Route;

/*
| Halaman publik — bisa dibuka siapa saja (hanya artikel Published).
*/
Route::get('/', [PublicArticleController::class, 'index'])->name('home');
Route::get('/artikel/{article}', [PublicArticleController::class, 'show'])->name('public.articles.show');

/*
| Khusus tamu (belum login). User yang sudah login otomatis
| diarahkan ke route "dashboard" oleh middleware guest.
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    // Nama "password.reset" wajib persis: dipakai notifikasi ResetPassword bawaan Laravel untuk membuat link di email.
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

/*
| Khusus user login. Tamu yang mengakses diarahkan ke route "login" oleh middleware auth.
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('dashboard')->group(function () {
        Route::get('laporan-artikel', ArticleReportController::class)->name('articles.report');
        Route::resource('articles', ArticleController::class);
    });
});
