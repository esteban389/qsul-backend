<?php

namespace App\Http\Controllers;

use App\DTOs\Survey\CreateSurveyRequestDto;
use App\Http\Requests\Survey\CreateSurveyRequest;
use App\Http\Services\QuestionService;
use App\Http\Services\SurveyService;
use App\Models\Question;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SurveyController extends Controller
{

    public function  __construct(public readonly SurveyService $surveyService, public readonly QuestionService $questionService)
    {
    }

    public function getCurrentSurvey()
    {
        return $this->surveyService->getCurrentSurvey();
    }

    public function createSurvey(CreateSurveyRequest $request)
    {
        $requestDto = CreateSurveyRequestDto::fromRequest($request);
        return DB::transaction(function () use ($requestDto) {
            $survey = $this->surveyService->createSurvey($requestDto);
            $this->questionService->createSurveyQuestions($requestDto, $survey);
            return $survey->load('questions');
        });
    }

    public function deleteQuestion(Question $question): Response
    {
        Gate::authorize('delete', $question);
        $this->questionService->deleteQuestion($question);
        return response()->noContent();
    }
}
