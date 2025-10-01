<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\Notifications;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\UniversityController;
use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return $user;
    });

    Route::controller(AuthenticationController::class)->group(function () {
        Route::get('/users', 'getUsers');
        Route::get('/users/{user}', 'getUserById');
        Route::delete('/users/{user}', 'deleteUser');
        Route::patch('/users/{user}', 'restoreUser')->withTrashed();
        Route::post('/profile', 'updateProfile');
        Route::post('/profile/password', 'updatePassword');
        Route::post('/profile/request-change', 'requestProfileChange');
        Route::get('/profile/pending-changes', 'pendingProfileChanges');
        Route::post('/profile/approve-change/{id}', 'approveProfileChange');
        Route::get('/profile/my-requests', 'myProfileChangeRequests');
        Route::get('/profile/office-url', 'getOfficeUrl');
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
        Route::get('/processes/{process}/url','getProcessUrl');

        Route::post('/services', 'createService');
        Route::post('/services/{service}', 'updateService')->withTrashed();
        Route::delete('/services/{service}', 'deleteService');
        Route::patch('/services/{service}', 'restoreService')->withTrashed();

        Route::post('/employees', 'createEmployee');
        Route::get('/employees/id/{employee}', 'getEmployeeById');
        Route::post('/employees/{employee}', 'updateEmployee')->withTrashed();
        Route::delete('/employees/{employee}', 'deleteEmployee');
        Route::patch('/employees/{employee}', 'restoreEmployee')->withTrashed();
        Route::post('/employees/{employee}/services', 'addEmployeeService');
        Route::delete('/employees/{employee}/services/{service}', 'removeEmployeeService');
        Route::get('/employees/{employee}/url','getEmployeeUrl');
    });

    Route::controller(SurveyController::class)->group(function () {
        Route::post('/survey', 'createSurvey');
        Route::delete('/survey/questions/{question}', 'deleteQuestion');
        Route::post('/survey/questions/{question}', 'updateQuestion');
        Route::post('/survey/questions/services/{service}', 'createServiceQuestion');
        Route::get('/survey/versions', 'getSurveys');
        Route::get('/survey/versions/{survey}', 'getSurveyById');
        Route::get('/survey/stats', 'getSurveyStats');

        Route::get('/answers', 'getAnswers');
        Route::get('/answers/{answer}', 'getAnswerById')->withTrashed();
        Route::post('/answers/{answer}/observations', 'addObservation');
        Route::post('/answers/{answer}/solve', 'solveAnswer');
        Route::post('/answers/{answer}/ignore', 'ignoreAnswer');
        Route::post('/answers/{answer}/restore', 'restoreAnswer')->withTrashed();
        Route::delete('observations/{observation}', 'deleteObservation');

        Route::post('/respondent-types', 'createRespondentType');
        Route::delete('/respondent-types/{respondentType}', 'deleteRespondentType');
    });


    Route::controller(Notifications::class)->group(function () {
        Route::get('/notifications', 'notifications');
        Route::get('/notifications/unread', 'unReadNotifications');
        Route::post('/notifications/read', 'readNotifications');
        Route::post('/notifications/{notification}', 'markAsRead');
    });


    Route::post('/chart/perception-group',[ChartController::class,'getPerceptionTrendByGroup']);
    Route::post('/chart/perception-trend',[ChartController::class,'getPerceptionTrend']);
    Route::post('/chart/perception-question',[ChartController::class,'getAverageByQuestionAndGroup']);
    Route::post('/chart/perception',[ChartController::class,'getPerceptionByGroup']);
    Route::post('/chart/volume',[ChartController::class,'getVolumeByGroup']);
    Route::post('/chart/volume-trend',[ChartController::class,'getVolumeTrendByGroup']);
    Route::post('/chart/distribution',[ChartController::class,'getDistributionByGroup']);
    Route::post('/chart/ranking',[ChartController::class,'getRankingOfGroup']);
    Route::post('/chart/respondent',[ChartController::class,'getRespondentByGroup']);

    //Auditing routes
    Route::get('/audits', function () {
        return Audit::latest()->with('author')->get();
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
