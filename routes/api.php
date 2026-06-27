<?php

use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\MenuItemController as AdminMenuItemController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Api\V1\Admin\RiderController as AdminRiderController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Customer\CartController as CustomerCartController;
use App\Http\Controllers\Api\V1\Customer\CheckoutController as CustomerCheckoutController;
use App\Http\Controllers\Api\V1\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Api\V1\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\Public\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\V1\Public\MenuItemController as PublicMenuItemController;
use App\Http\Controllers\Api\V1\Public\RestaurantController as PublicRestaurantController;
use App\Http\Controllers\Api\V1\Rider\DeliveryController as RiderDeliveryController;
use App\Http\Controllers\Api\V1\Rider\ProfileController as RiderProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->middleware('throttle:api-public');

Route::prefix('v1')->middleware('request.id')->group(function (): void {
    Route::middleware('throttle:api-public')->group(function (): void {
        Route::get('/restaurant', [PublicRestaurantController::class, 'show']);
        Route::get('/categories', [PublicCategoryController::class, 'index']);
        Route::get('/menu-items', [PublicMenuItemController::class, 'index']);
        Route::get('/menu-items/{menuItem}', [PublicMenuItemController::class, 'show']);
    });

    Route::prefix('auth')->middleware('throttle:api-auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth:sanctum', 'api.active'])->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);

        Route::post('/devices', [DeviceController::class, 'store'])->middleware('throttle:api-customer');
        Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])->middleware('throttle:api-customer');

        Route::prefix('customer')
            ->middleware(['api.role:customer', 'api.ability:customer', 'throttle:api-customer'])
            ->group(function (): void {
                Route::get('/profile', [CustomerProfileController::class, 'show']);
                Route::put('/profile', [CustomerProfileController::class, 'update']);

                Route::get('/cart', [CustomerCartController::class, 'show']);
                Route::post('/cart/items/{menuItem}', [CustomerCartController::class, 'store']);
                Route::put('/cart/items/{cartItem}', [CustomerCartController::class, 'update']);
                Route::delete('/cart/items/{cartItem}', [CustomerCartController::class, 'destroy']);
                Route::delete('/cart', [CustomerCartController::class, 'clear']);

                Route::post('/checkout', [CustomerCheckoutController::class, 'store'])->middleware('throttle:api-checkout');
                Route::get('/orders', [CustomerOrderController::class, 'index']);
                Route::get('/orders/{order}', [CustomerOrderController::class, 'show']);
            });

        Route::prefix('rider')
            ->middleware(['api.role:rider', 'api.ability:rider', 'throttle:api-rider'])
            ->group(function (): void {
                Route::get('/profile', [RiderProfileController::class, 'show']);
                Route::put('/profile', [RiderProfileController::class, 'update']);
                Route::get('/dashboard', [RiderDeliveryController::class, 'dashboard']);
                Route::get('/deliveries', [RiderDeliveryController::class, 'index']);
                Route::get('/deliveries/{order}', [RiderDeliveryController::class, 'show']);
                Route::post('/deliveries/{order}/status', [RiderDeliveryController::class, 'updateStatus'])->middleware('throttle:api-status-update');
            });

        Route::prefix('admin')
            ->middleware(['api.role:admin', 'api.ability:admin', 'throttle:api-admin'])
            ->group(function (): void {
                Route::get('/dashboard', AdminDashboardController::class);
                Route::get('/restaurant', [AdminRestaurantController::class, 'show']);
                Route::put('/restaurant', [AdminRestaurantController::class, 'update'])->middleware('throttle:api-upload');

                Route::get('/orders', [AdminOrderController::class, 'index']);
                Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
                Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
                Route::post('/orders/{order}/assign-rider', [AdminOrderController::class, 'assignRider']);
                Route::delete('/orders/{order}/unassign-rider', [AdminOrderController::class, 'unassignRider']);

                Route::apiResource('riders', AdminRiderController::class)->parameters(['riders' => 'rider']);
                Route::apiResource('categories', AdminCategoryController::class)->parameters(['categories' => 'category']);
                Route::apiResource('menu-items', AdminMenuItemController::class)->parameters(['menu-items' => 'menuItem']);
            });
    });
});
