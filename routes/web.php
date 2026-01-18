<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// トップページにアクセスしたらダッシュボードを表示
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// もしログイン機能（Breeze）を活かしたい場合のための予備（今は使わなくてもOK）
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
