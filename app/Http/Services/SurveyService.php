<?php

namespace App\Http\Services;

use App\DTOs\Survey\CreateSurveyRequestDto;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Answer;
use Illuminate\Support\Facades\Auth;

readonly class SurveyService
{

    public function getCurrentSurvey()
    {
        return Survey::query()->latest('version')->with('questions.service')->firstOrFail();
    }

    public function createSurvey(CreateSurveyRequestDto $requestDto)
    {
        $newVersionNumber = Survey::query()->max('version') + 1;
        return Survey::query()->create(['version' => $newVersionNumber]);
    }

    public function getSurveys(): Collection|\Illuminate\Support\Collection
    {
        return Survey::query()->latest('version')->with('questions')->get();
    }
    
    public function getSurveyStats(string $timeFilter)
    {
        $query = Answer::query();
        $currentSurvey = $this->getCurrentSurvey();

        $query->where('survey_id', $currentSurvey->id);
        
        if(Auth::user()->hasRole(UserRole::CampusCoordinator)) {
            $query->whereHas('employeeService.employee', function ($query) use ($user) {
                $query->where('campus_id', $user->campus_id);
            });
        }

        if(Auth::user()->hasRole(UserRole::ProcessLeader)) {
            $query->whereHas('employeeService.employee', function ($query) use ($user) {
                $query->where('process_id', $user->employee()->first()->process_id);
                $query->where('campus_id', $user->campus_id);
            });
        }

        if ($timeFilter === 'month') {
            $query->where('created_at', '>=', now()->subMonth());
        } elseif ($timeFilter === '30days') {
            $query->where('created_at', '>=', now()->subDays(30));
        } elseif ($timeFilter === '7days') {
            $query->where('created_at', '>=', now()->subDays(7));
        }

        $totalSubmissions = $query->count();
        //Ignored submissions are those that have been soft deleted
        $ignoredSubmissionsQuery = $query->withTrashed()->whereNotNull('deleted_at');
        if($timeFilter === 'month') {
            $ignoredSubmissionsQuery->where('deleted_at', '>=', now()->subMonth());
        } elseif ($timeFilter === '30days') {
            $ignoredSubmissionsQuery->where('deleted_at', '>=', now()->subDays(30));
        } elseif ($timeFilter === '7days') {
            $ignoredSubmissionsQuery->where('deleted_at', '>=', now()->subDays(7));
        }

        $ignoredSubmissions = $ignoredSubmissionsQuery->count();

        return [
            'total_submissions' => $totalSubmissions,
            'ignored_submissions' => $ignoredSubmissions,
        ];
    }
}
