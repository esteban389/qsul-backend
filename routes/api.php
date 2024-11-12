<?php

use App\Http\Controllers\AuthenticationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/users', [AuthenticationController::class, 'ShowUsers']);
    Route::get('/users/{user}', [AuthenticationController::class, 'getUserById']);
    Route::delete('/users/{user}', [AuthenticationController::class, 'deleteUser']);
    Route::patch('/users/{user}', [AuthenticationController::class, 'restoreUser'])->withTrashed();
});

