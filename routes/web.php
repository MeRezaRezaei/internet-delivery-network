<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HostManagerController;
use App\Http\Controllers\MarzbanSubscriptionController;

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\SubHostAdminController;
use App\Http\Middleware\AdminMiddleware;

// 1. Specific Admin Routes (Vue SPA - Legacy or separate)
Route::get('/sub/admin-vue', function () {
    return view('sub.admin');
});

// 2. New Blade-based Admin Panel
Route::prefix('sub/admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('/dashboard', [SubHostAdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/hosts', [SubHostAdminController::class, 'store'])->name('admin.hosts.store');
        Route::put('/hosts/{subHost}', [SubHostAdminController::class, 'update'])->name('admin.hosts.update');
        Route::delete('/hosts/{subHost}', [SubHostAdminController::class, 'destroy'])->name('admin.hosts.destroy');
    });
});

Route::prefix('sub/admin-api')->group(function () {
    Route::get('/hosts', [HostManagerController::class, 'index']);
    Route::post('/hosts', [HostManagerController::class, 'store']);
    Route::get('/hosts/{subHost}', [HostManagerController::class, 'show']);
    Route::put('/hosts/{subHost}', [HostManagerController::class, 'update']);
    Route::delete('/hosts/{subHost}', [HostManagerController::class, 'destroy']);
});

// 2. Subscription Route
Route::get('/sub/{token}', [MarzbanSubscriptionController::class, 'show'])
    ->where('token', '^(?!admin$).*')
    ->name('marzban.sub.show');
