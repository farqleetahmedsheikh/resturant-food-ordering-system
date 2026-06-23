<?php

use App\Http\Controllers\Rider\RiderDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'rider'])
    ->prefix('rider')
    ->name('rider.')
    ->group(function (): void {
        Route::get('/dashboard', [RiderDashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [RiderDashboardController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [RiderDashboardController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/update-status', [RiderDashboardController::class, 'updateStatus'])->name('orders.update-status');
    });
