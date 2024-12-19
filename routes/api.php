<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\SurveyController;
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
        Route::post('/campuses/{campus}', 'updateCampus')->withTrashed();
        Route::delete('/campuses/{campus}', 'deleteCampus');
        Route::patch('/campuses/{campus}', 'restoreCampus')->withTrashed();

        Route::post('/processes', 'createProcess');
        Route::post('/processes/{process}', 'updateProcess')->withTrashed();
        Route::delete('/processes/{process}', 'deleteProcess');
        Route::patch('/processes/{process}', 'restoreProcess')->withTrashed();

        Route::post('/services', 'createService');
        Route::post('/services/{service}', 'updateService')->withTrashed();
        Route::delete('/services/{service}', 'deleteService');
        Route::patch('/services/{service}', 'restoreService')->withTrashed();

        Route::post('/employees', 'createEmployee');
        Route::post('/employees/{employee}', 'updateEmployee')->withTrashed();
        Route::delete('/employees/{employee}', 'deleteEmployee');
        Route::patch('/employees/{employee}', 'restoreEmployee')->withTrashed();
        Route::post('/employees/{employee}/services', 'addEmployeeService');
        Route::delete('/employees/{employee}/services/{service}', 'removeEmployeeService');
    });

    Route::controller(SurveyController::class)->group(function (){
        Route::post('/survey', 'createSurvey');
        Route::delete('/survey/questions/{question}', 'deleteQuestion');
    });
});

    Route::get('/campuses', [UniversityController::class,'getCampuses']);
    Route::get('/campuses/{campus:token}', [UniversityController::class,'getCampusById']);

    Route::get('/processes', [UniversityController::class,'getProcesses']);
    Route::get('/processes/{process:token}', [UniversityController::class,'getProcessById']);

    Route::get('/services', [UniversityController::class,'getServices']);
    Route::get('/services/{service:token}', [UniversityController::class,'getServiceById']);

    Route::get('/employees', [UniversityController::class,'getEmployees']);
    Route::get('/employees/{employee:token}', [UniversityController::class,'getEmployeeById']);
    Route::get('/employees/{employee:token}/services', [UniversityController::class,'getEmployeeServices']);

    Route::get('survey', [SurveyController::class,'getCurrentSurvey']);
