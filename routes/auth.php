<?php

use App\Http\Controllers\AuthenticationController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthenticationController::class, 'Register'])
    ->middleware('auth')
    ->name('register');

Route::post('/login', [AuthenticationController::class, 'Login'])
    ->middleware('guest')
    ->name('login');

Route::post('/forgot-password', [AuthenticationController::class, 'ForgotPassword'])
    ->middleware('guest')
    ->name('password.email');

Route::post('/reset-password', [AuthenticationController::class, 'ResetPassword'])
    ->middleware('guest')
    ->name('password.store');

Route::post('/logout', [AuthenticationController::class, 'Logout'])
    ->middleware('auth')
    ->name('logout');
