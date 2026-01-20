<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// 1. 未ログイン時に「/」にアクセスしたらログイン画面へ飛ばす
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. ログイン（auth）済みユーザーだけがアクセスできるグループ
Route::middleware(['auth', 'verified'])->group(function () {

    // ダッシュボード（マイページ）
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // プロフィール管理（Breeze標準）
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
