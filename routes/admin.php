<?php

use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMenuItemController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminRestaurantSettingsController;
use App\Http\Controllers\Admin\AdminRiderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('/orders/{order}/assign-rider', [AdminOrderController::class, 'assignRider'])->name('orders.assign-rider');
        Route::delete('/orders/{order}/unassign-rider', [AdminOrderController::class, 'unassignRider'])->name('orders.unassign-rider');

        Route::get('/settings/restaurant', [AdminRestaurantSettingsController::class, 'edit'])->name('settings.restaurant.edit');
        Route::put('/settings/restaurant', [AdminRestaurantSettingsController::class, 'update'])->name('settings.restaurant.update');

        Route::resource('menu-items', AdminMenuItemController::class)->except(['show']);
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::resource('riders', AdminRiderController::class)->except(['show']);
    });
