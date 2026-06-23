<?php

use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetOtpController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\MenuController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/menu', [MenuController::class, 'index'])->name('menu');
Route::get('/menu/{menuItem:slug}', [MenuController::class, 'show'])->name('menu.show');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('/forgot-password', [PasswordResetOtpController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetOtpController::class, 'sendOtp'])->name('password.otp.send');
    Route::get('/forgot-password/verify', [PasswordResetOtpController::class, 'verifyForm'])->name('password.otp');
    Route::post('/forgot-password/verify', [PasswordResetOtpController::class, 'verifyOtp'])->name('password.otp.verify');
    Route::get('/reset-password', [PasswordResetOtpController::class, 'resetForm'])->name('password.reset.form');
    Route::post('/reset-password', [PasswordResetOtpController::class, 'resetPassword'])->name('password.reset.update');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/account/security', [PasswordController::class, 'edit'])->name('account.security');
    Route::put('/account/security/password', [PasswordController::class, 'update'])->name('account.password.update');
});

Route::middleware('customer')->group(function (): void {
    Route::post('/cart/add/{menuItem}', [CartController::class, 'add'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
});
