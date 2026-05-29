<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\IDN\DashboardController;
use App\Http\Controllers\IDN\TunnelController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/idn', [DashboardController::class, 'index'])->name('idn.dashboard');
Route::get('/idn/api/logs', [DashboardController::class, 'logs'])->name('idn.api.logs');
Route::get('/idn/api/routing', [DashboardController::class, 'routing'])->name('idn.api.routing');
Route::get('/idn/api/traffic', [DashboardController::class, 'traffic'])->name('idn.api.traffic');
Route::post('/idn/tunnels', [TunnelController::class, 'store'])->name('idn.tunnels.store');
Route::delete('/idn/tunnels/{tunnel}', [TunnelController::class, 'destroy'])->name('idn.tunnels.destroy');
