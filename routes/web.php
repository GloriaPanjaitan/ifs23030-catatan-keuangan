<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FinancialRecordController; // Kita pakai controller ini
use Illuminate\Support\Facades\Route;

// =========================================================
// ROUTE GROUP AUTHENTIKASI
// =========================================================
Route::group(['prefix' => 'auth'], function () {
    Route::get('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::get('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');
});

// =========================================================
// ROUTE GROUP APLIKASI UTAMA (MEMBUTUHKAN LOGIN)
// =========================================================
Route::group(['prefix' => 'app', 'middleware' => 'check.auth'], function () {
    
    // ROUTE UTAMA SEKARANG ADALAH CATATAN KEUANGAN
    // URL: /app/home
    Route::get('/home', [FinancialRecordController::class, 'index'])->name('app.home');
});

// =========================================================
// ROUTE ROOT (REDIRECT)
// =========================================================
Route::get('/', function () {
    // Redirect langsung ke /app/home
    return redirect()->route('app.home');
});