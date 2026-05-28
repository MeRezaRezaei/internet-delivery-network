<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\IDN\DashboardController;
use App\Http\Controllers\IDN\TunnelController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/idn', [DashboardController::class, 'index'])->name('idn.dashboard');
Route::post('/idn/dns/toggle', [DashboardController::class, 'toggleDnsBlocklist'])->name('idn.dns.toggle');
Route::get('/idn/api/logs', [DashboardController::class, 'logs'])->name('idn.api.logs');
Route::post('/idn/tunnels', [TunnelController::class, 'store'])->name('idn.tunnels.store');
Route::delete('/idn/tunnels/{tunnel}', [TunnelController::class, 'destroy'])->name('idn.tunnels.destroy');
