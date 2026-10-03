<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Auth;

// Guest routes (not logged in)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Auth routes (logged in)
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});


// Root route - check if logged in first
Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'admin') {
            return redirect('/admin/dashboard');
        }
        return redirect('/cashier/pos');
    }
    return redirect('/login');
});

Route::get('/forgot-password', [PasswordResetController::class, 'showPhoneForm'])->name('forget-password');
Route::post('/forgot-password/send-otp', [PasswordResetController::class, 'sendOtp'])->name('password.send-otp');
Route::get('/forgot-password/verify-otp', [PasswordResetController::class, 'showOtpForm'])->name('password.verify-otp');
Route::post('/forgot-password/verify-otp', [PasswordResetController::class, 'verifyOtp'])->name('password.verify-otp.submit');

Route::post('/cashier/pin-login', [AuthenticatedSessionController::class, 'pinLogin'])->name('cashier.pin-login');

Route::middleware('auth')->group(function () {
    Route::get('/forgot-password/reset-or-skip', [PasswordResetController::class, 'showResetForm'])->name('password.reset-or-skip');
    Route::post('/forgot-password/reset', [PasswordResetController::class, 'resetPassword'])->name('password.reset.submit');
});
