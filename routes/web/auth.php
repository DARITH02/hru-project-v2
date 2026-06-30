<?php

use App\Http\Controllers\Auth\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/forgot-password', [WebAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password/email-otp', [WebAuthController::class, 'sendPasswordResetOtp'])->middleware('throttle:5,1')->name('password.email-otp');
    Route::get('/reset-password', [WebAuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
    Route::post('/demo-login', [WebAuthController::class, 'demoLogin'])->middleware('throttle:3,1')->name('demo.login');
});

Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
Route::post('/register/email-otp', [WebAuthController::class, 'sendRegistrationOtp'])->middleware('throttle:5,1')->name('register.email-otp');
Route::post('/register', [WebAuthController::class, 'register'])->middleware('throttle:3,1')->name('register.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
