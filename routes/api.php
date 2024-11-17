<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\UniversityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::controller(AuthenticationController::class)->group(function (){
        Route::get('/users', 'getUsers');
        Route::get('/users/{user}', 'getUserById');
        Route::delete('/users/{user}', 'deleteUser');
        Route::patch('/users/{user}', 'restoreUser')->withTrashed();
    });

    Route::controller(UniversityController::class)->group(function (){
        Route::post('/campuses', 'createCampus');
        Route::put('/campuses/{campus}', 'updateCampus');
        Route::delete('/campuses/{campus}', 'deleteCampus');
        Route::patch('/campuses/{campus}', 'restoreCampus')->withTrashed();

        Route::post('/processes', 'createProcess');
        Route::put('/processes/{process}', 'updateProcess');
        Route::delete('/processes/{process}', 'deleteProcess');
        Route::patch('/processes/{process}', 'restoreProcess')->withTrashed();

        Route::post('/services', 'createService');
        Route::put('/services/{service}', 'updateService');
        Route::delete('/services/{service}', 'deleteService');
        Route::patch('/services/{service}', 'restoreService')->withTrashed();
    });
});

Route::group(['middleware' => 'guest'], function () {
    Route::get('/campuses', [UniversityController::class,'getCampuses']);
    Route::get('/campuses/{campus}', [UniversityController::class,'getCampusById']);

    Route::get('/processes', [UniversityController::class,'getProcesses']);
    Route::get('/processes/{process}', [UniversityController::class,'getProcessById']);

    Route::get('/services', [UniversityController::class,'getServices']);
    Route::get('/services/{service}', [UniversityController::class,'getServiceById']);
});
