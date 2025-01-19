<?php

namespace App\Http\Controllers;

use App\DTOs\Survey\AddObservationRequestDto;
use App\DTOs\Survey\AnswerSurveyRequestDto;
use App\DTOs\Survey\CreateRespondentTypeRequestDto;
use App\DTOs\Survey\CreateServiceQuestionRequestDto;
use App\DTOs\Survey\CreateSurveyRequestDto;
use App\DTOs\Survey\UpdateQuestionRequestDto;
use App\Http\Requests\Survey\AddObservationRequest;
use App\Http\Requests\Survey\AnswerSurveyRequest;
use App\Http\Requests\Survey\CreateRespondentTypeRequest;
use App\Http\Requests\Survey\CreateServiceQuestionRequest;
use App\Http\Requests\Survey\CreateSurveyRequest;
use App\Http\Requests\Survey\UpdateQuestionRequest;
use App\Http\Services\AnswerService;
use App\Http\Services\ObservationService;
use App\Http\Services\QuestionService;
use App\Http\Services\RespondentTypeService;
use App\Http\Services\SurveyService;
use App\Models\Answer;
use App\Models\Question;
use App\Models\RespondentType;
use App\Models\Service;
use App\Models\Survey;
use App\Models\Observation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SurveyController extends Controller
{

    public function __construct(private readonly SurveyService $surveyService, private readonly QuestionService $questionService, private readonly AnswerService $answerService, private readonly ObservationService $observationService, private readonly RespondentTypeService $respondentService)
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

    public function getAnswers()
    {
        return $this->answerService->getAnswers();
    }

    public function getAnswerById(Answer $answer)
    {
        return $answer->load([
            'observations',
            'observations.user',
            'answerQuestions',
            'answerQuestions.question',
            'respondentType',
            'survey',
            'employeeService',
            'employeeService.employee',
            'employeeService.service',
        ]);
    }

    public function getAnswerObservations(Answer $answer)
    {
        return $answer->observations;
    }

    public function addObservation(Answer $answer, AddObservationRequest $request)
    {
        $requestDto = AddObservationRequestDto::fromRequest($request);
        DB::transaction(function () use ($answer, $requestDto) {
            $this->observationService->addObservationToAnswer($requestDto, $answer);
        });
        return response()->created();
    }

    public function deleteObservation(Observation $observation)
    {
        Gate::authorize('delete', $observation);
        $this->observationService->deleteObservation($observation);
    }

    public function ignoreAnswer(Answer $answer, AddObservationRequest $request)
    {
        Gate::authorize('ignore', $answer);
        $requestDto = AddObservationRequestDto::fromRequest($request);
        DB::transaction(function () use ($answer, $requestDto) {
            $this->answerService->delete($answer);
            $this->observationService->addObservationToAnswer($requestDto, $answer);
        });
        return \response()->noContent();
    }

    public function restoreAnswer(Answer $answer)
    {
        Gate::authorize('restore', $answer);
        $this->answerService->restore($answer);
        return response()->noContent();
    }

    public function createAnswer(AnswerSurveyRequest $request)
    {
        $requestDto = AnswerSurveyRequestDto::fromRequest($request);
        DB::transaction(function () use ($requestDto) {
            $this->answerService->createAnswers($requestDto);
        });
        return response()->created();
    }

    public function getRespondentTypes()
    {
        return response()->json($this->respondentService->getRespondentTypes());
    }

    public function createRespondentType(CreateRespondentTypeRequest $request)
    {
        $requestDto = CreateRespondentTypeRequestDto::fromRequest($request);
        $this->respondentService->createRespondentType($requestDto);
        return response()->created();
    }

    public function deleteRespondentType(RespondentType $respondentType)
    {
        Gate::authorize('delete', $respondentType);
        $this->respondentService->deleteRespondentType($respondentType);
        return response()->noContent();
    }
}
