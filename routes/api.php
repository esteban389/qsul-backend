<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Notifications;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\UniversityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::controller(AuthenticationController::class)->group(function () {
        Route::get('/users', 'getUsers');
        Route::get('/users/{user}', 'getUserById');
        Route::delete('/users/{user}', 'deleteUser');
        Route::patch('/users/{user}', 'restoreUser')->withTrashed();
    });

    Route::controller(UniversityController::class)->group(function () {
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

    Route::controller(SurveyController::class)->group(function () {
        Route::post('/survey', 'createSurvey');
        Route::delete('/survey/questions/{question}', 'deleteQuestion');
        Route::post('/survey/questions/{question}', 'updateQuestion');
        Route::post('/survey/questions/services/{service}', 'createServiceQuestion');
        Route::get('/survey/versions', 'getSurveys');
        Route::get('/survey/versions/{survey}', 'getSurveyById');

        Route::get('/answers', 'getAnswers');
        Route::get('/answers/{answer}', 'getAnswerById')->withTrashed();
        Route::post('/answers/{answer}/observations', 'addObservation');
        Route::post('/answers/{answer}/ignore', 'ignoreAnswer');
        Route::post('/answers/{answer}/restore', 'restoreAnswer')->withTrashed();
        //TODO: Implement
        Route::delete('observations/{observation}', 'deleteObservation');

        Route::post('/respondent-types', 'createRespondentType');
        Route::delete('/respondent-types/{respondentType}', 'deleteRespondentType');
    });

    /*TODO implement profile management routes
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'getProfile');
        Route::post('/profile', 'updateProfile');
        Route::post('/profile/password', 'updatePassword');
    });
    */

    Route::controller(Notifications::class)->group(function () {
        Route::get('/notifications', 'notifications');
        Route::get('/notifications/unread', 'unReadNotifications');
        Route::post('/notifications/read', 'readNotifications');
        Route::post('/notifications/{notification}', 'markAsRead');
    });

    //Auditing routes
    Route::get('/audits', function () {
        return \OwenIt\Auditing\Models\Audit::latest()->get();
    });

    Route::get('/audits/{audit}', function (\OwenIt\Auditing\Models\Audit $audit) {
        return $audit->getMetadata();
    });
});

Route::get('/campuses', [UniversityController::class, 'getCampuses']);
Route::get('/campuses/{campus:token}', [UniversityController::class, 'getCampusById']);

Route::get('/processes', [UniversityController::class, 'getProcesses']);
Route::get('/processes/{process:token}', [UniversityController::class, 'getProcessById']);

Route::get('/services', [UniversityController::class, 'getServices']);
Route::get('/services/{service:token}', [UniversityController::class, 'getServiceById']);

Route::get('/employees', [UniversityController::class, 'getEmployees']);
Route::get('/employees/{employee:token}', [UniversityController::class, 'getEmployeeById']);
Route::get('/employees/{employee:token}/services', [UniversityController::class, 'getEmployeeServices']);

Route::get('survey', [SurveyController::class, 'getCurrentSurvey']);
Route::post('/answers', [SurveyController::class, 'createAnswer']);

Route::get('/respondent-types', [SurveyController::class, 'getRespondentTypes']);
