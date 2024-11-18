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
        Route::put('/campuses/{campus:token}', 'updateCampus');
        Route::delete('/campuses/{campus:token}', 'deleteCampus');
        Route::patch('/campuses/{campus:token}', 'restoreCampus')->withTrashed();

        Route::post('/processes', 'createProcess');
        Route::put('/processes/{process:token}', 'updateProcess');
        Route::delete('/processes/{process:token}', 'deleteProcess');
        Route::patch('/processes/{process:token}', 'restoreProcess')->withTrashed();

        Route::post('/services', 'createService');
        Route::put('/services/{service:token}', 'updateService');
        Route::delete('/services/{service:token}', 'deleteService');
        Route::patch('/services/{service:token}', 'restoreService')->withTrashed();

        Route::post('/employees', 'createEmployee');
        Route::put('/employees/{employee:token}', 'updateEmployee');
        Route::delete('/employees/{employee:token}', 'deleteEmployee');
        Route::patch('/employees/{employee:token}', 'restoreEmployee')->withTrashed();
        Route::post('/employees/{employee:token}/services', 'addEmployeeService');
    });
});

Route::group(['middleware' => 'guest'], function () {
    Route::get('/campuses', [UniversityController::class,'getCampuses']);
    Route::get('/campuses/{campus:token}', [UniversityController::class,'getCampusById']);

    Route::get('/processes', [UniversityController::class,'getProcesses']);
    Route::get('/processes/{process:token}', [UniversityController::class,'getProcessById']);

    Route::get('/services', [UniversityController::class,'getServices']);
    Route::get('/services/{service:token}', [UniversityController::class,'getServiceById']);

    Route::get('/employees', [UniversityController::class,'getEmployees']);
    Route::get('/employees/{employee:token}', [UniversityController::class,'getEmployeeById']);
    Route::get('/employees/{employee:token}/services', [UniversityController::class,'getEmployeeServices']);
});
