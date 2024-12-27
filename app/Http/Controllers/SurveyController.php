<?php

namespace App\Http\Controllers;

use App\DTOs\Survey\CreateServiceQuestionRequestDto;
use App\DTOs\Survey\CreateSurveyRequestDto;
use App\DTOs\Survey\UpdateQuestionRequestDto;
use App\Http\Requests\Survey\CreateServiceQuestionRequest;
use App\Http\Requests\Survey\CreateSurveyRequest;
use App\Http\Requests\Survey\UpdateQuestionRequest;
use App\Http\Services\QuestionService;
use App\Http\Services\SurveyService;
use App\Models\Question;
use App\Models\Service;
use App\Models\Survey;
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

    public function updateQuestion(Question $question, UpdateQuestionRequest $request): Response
    {
        $requestDto = UpdateQuestionRequestDto::fromRequest($request);
        DB::transaction(function () use ($question, $requestDto) {
            $this->questionService->updateQuestion($question, $requestDto);
        });

        return response()->noContent();
    }

    public function createServiceQuestion(Service $service, CreateServiceQuestionRequest $request): Response
    {
        $requestDto = CreateServiceQuestionRequestDto::fromRequest($request);
        DB::transaction(function () use ($service, $requestDto) {
            $survey = $this->getCurrentSurvey();
            $this->questionService->createServiceQuestion($service, $requestDto, $survey);
        });
        return response()->created();
    }

    public function getSurveys()
    {
        Gate::authorize('view', Survey::class);
        return \response()->json($this->surveyService->getSurveys());
    }

    public function getSurveyById(Survey $survey)
    {
        Gate::authorize('view', $survey);
        return $survey->load('questions');
    }
}
