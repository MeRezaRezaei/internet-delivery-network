<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HostManagerController;
use App\Http\Controllers\MarzbanSubscriptionController;

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\SubHostAdminController;
use App\Http\Middleware\AdminMiddleware;

// 1. Specific Admin Routes (Vue SPA - Legacy or separate)
Route::get('/sub-dev/create-db', function () {
    try {
        \DB::statement('CREATE DATABASE IF NOT EXISTS idn_db');
        return response()->json([
            'status' => 'created',
            'message' => 'Database idn_db created successfully.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/sub-dev/db-diagnose', function () {
    try {
        $databases = \DB::select('SHOW DATABASES');
        $currentDb = \DB::connection()->getDatabaseName();
        return response()->json([
            'status' => 'connected',
            'current_database' => $currentDb,
            'all_databases' => array_column($databases, 'Database'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/sub/admin-vue', function () {
    return view('sub.admin');
});

// 2. New Blade-based Admin Panel
Route::get('/sub/admin', function () {
    return redirect()->route('admin.login');
});

Route::prefix('sub/admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('/dashboard', [SubHostAdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/hosts', [SubHostAdminController::class, 'store'])->name('admin.hosts.store');
        Route::put('/hosts/{subHost}', [SubHostAdminController::class, 'update'])->name('admin.hosts.update');
        Route::delete('/hosts/{subHost}', [SubHostAdminController::class, 'destroy'])->name('admin.hosts.destroy');

        // Profile CRUD routes
        Route::post('/profiles', [SubHostAdminController::class, 'storeProfile'])->name('admin.profiles.store');
        Route::put('/profiles/{subProfile}', [SubHostAdminController::class, 'updateProfile'])->name('admin.profiles.update');
        Route::delete('/profiles/{subProfile}', [SubHostAdminController::class, 'destroyProfile'])->name('admin.profiles.destroy');
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

Route::get('/sub-dev/{token}', [MarzbanSubscriptionController::class, 'showDev'])
    ->where('token', '^(?!admin$).*')
    ->name('marzban.sub.show_dev');

