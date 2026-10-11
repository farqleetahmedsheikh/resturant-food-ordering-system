<?php

use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminAddonUpsellController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFinanceController;
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
        Route::get('/dashboard/live', [AdminDashboardController::class, 'live'])->name('dashboard.live');
        Route::get('/finance', [AdminFinanceController::class, 'index'])->name('finance');
        Route::post('/dashboard/orders/{order}/confirm', [AdminDashboardController::class, 'confirmPendingOrder'])->name('dashboard.orders.confirm');
        Route::post('/dashboard/orders/{order}/decline', [AdminDashboardController::class, 'declinePendingOrder'])->name('dashboard.orders.decline');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('/orders/{order}/assign-rider', [AdminOrderController::class, 'assignRider'])->name('orders.assign-rider');
        Route::delete('/orders/{order}/unassign-rider', [AdminOrderController::class, 'unassignRider'])->name('orders.unassign-rider');

        Route::get('/settings/restaurant', [AdminRestaurantSettingsController::class, 'edit'])->name('settings.restaurant.edit');
        Route::put('/settings/restaurant', [AdminRestaurantSettingsController::class, 'update'])->name('settings.restaurant.update');
        Route::delete('/settings/restaurant/logo', [AdminRestaurantSettingsController::class, 'destroyLogo'])->name('settings.restaurant.logo.destroy');
        Route::delete('/settings/restaurant/cover-image', [AdminRestaurantSettingsController::class, 'destroyCoverImage'])->name('settings.restaurant.cover.destroy');

        Route::resource('menu-items', AdminMenuItemController::class)->except(['show']);
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::get('/addons-upselling', [AdminAddonUpsellController::class, 'index'])->name('addons-upselling.index');
        Route::get('/addons-upselling/addons', [AdminAddonUpsellController::class, 'addons'])->name('addons-upselling.addons.index');
        Route::get('/addons-upselling/groups', [AdminAddonUpsellController::class, 'groups'])->name('addons-upselling.groups.index');
        Route::get('/addons-upselling/assignments', [AdminAddonUpsellController::class, 'assignments'])->name('addons-upselling.assignments.index');
        Route::get('/addons-upselling/rules', [AdminAddonUpsellController::class, 'rules'])->name('addons-upselling.rules.index');
        Route::post('/addons-upselling/addons', [AdminAddonUpsellController::class, 'storeAddon'])->name('addons-upselling.addons.store');
        Route::put('/addons-upselling/addons/{addon}', [AdminAddonUpsellController::class, 'updateAddon'])->name('addons-upselling.addons.update');
        Route::delete('/addons-upselling/addons/{addon}', [AdminAddonUpsellController::class, 'destroyAddon'])->name('addons-upselling.addons.destroy');
        Route::post('/addons-upselling/groups', [AdminAddonUpsellController::class, 'storeGroup'])->name('addons-upselling.groups.store');
        Route::put('/addons-upselling/groups/{group}', [AdminAddonUpsellController::class, 'updateGroup'])->name('addons-upselling.groups.update');
        Route::post('/addons-upselling/assign-product', [AdminAddonUpsellController::class, 'assignProduct'])->name('addons-upselling.assign-product');
        Route::post('/addons-upselling/assign-category', [AdminAddonUpsellController::class, 'assignCategory'])->name('addons-upselling.assign-category');
        Route::post('/addons-upselling/assign-global', [AdminAddonUpsellController::class, 'assignGlobal'])->name('addons-upselling.assign-global');
        Route::post('/addons-upselling/rules', [AdminAddonUpsellController::class, 'storeRule'])->name('addons-upselling.rules.store');
        Route::put('/addons-upselling/rules/{rule}', [AdminAddonUpsellController::class, 'updateRule'])->name('addons-upselling.rules.update');
        Route::delete('/addons-upselling/rules/{rule}', [AdminAddonUpsellController::class, 'destroyRule'])->name('addons-upselling.rules.destroy');
        Route::resource('riders', AdminRiderController::class);
    });
