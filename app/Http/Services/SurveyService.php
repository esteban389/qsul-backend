<?php

namespace App\Http\Services;

use App\DTOs\Survey\CreateSurveyRequestDto;
use App\Models\Survey;

readonly class SurveyService
{

    public function getCurrentSurvey()
    {
        return Survey::query()->latest('version')->with('questions')->firstOrFail();
    }

    public function createSurvey(CreateSurveyRequestDto $requestDto)
    {
        $newVersionNumber = Survey::query()->max('version') + 1;
        return Survey::query()->create(['version' => $newVersionNumber]);
    }
}
