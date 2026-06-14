<?php

use App\Http\Controllers\Auth\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::post('/demo-login', [WebAuthController::class, 'demoLogin'])->middleware('throttle:3,1')->name('demo.login');
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->middleware('throttle:3,1')->name('register.post');
});

Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
