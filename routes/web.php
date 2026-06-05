<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HostManagerController;
use App\Http\Controllers\MarzbanSubscriptionController;

// 1. Specific Admin Routes
Route::get('/sub/admin', function () {
    return view('sub.admin');
});

Route::prefix('sub/admin')->group(function () {
    Route::get('/hosts', [HostManagerController::class, 'index']);
    Route::post('/hosts', [HostManagerController::class, 'store']);
    Route::get('/hosts/{subHost}', [HostManagerController::class, 'show']);
    Route::put('/hosts/{subHost}', [HostManagerController::class, 'update']);
    Route::delete('/hosts/{subHost}', [HostManagerController::class, 'destroy']);
    
    // Vue SPA catch-all (nested)
    Route::get('/{any}', function () {
        return view('sub.admin');
    })->where('any', '.*');
});

// 2. Subscription Route
Route::get('/sub/{token}', [MarzbanSubscriptionController::class, 'show'])
    ->where('token', '^(?!admin$).*')
    ->name('marzban.sub.show');
