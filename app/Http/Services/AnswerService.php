<?php

namespace App\Http\Services;

use App\DTOs\Auth\UserRole;
use App\DTOs\Survey\AnswerSurveyRequestDto;
use App\Events\SurveyCompletion;
use App\Models\Answer;
use App\Models\EmployeeService as EmployeeServiceModel;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;

readonly class AnswerService
{

    public function __construct(public RespondentTypeService $respondentTypeService, public SurveyService $surveyService)
    {
    }

    public function getAnswers()
    {
        // Get all answers with their relationships ordered by creation date and survey version
        $query = Answer::withTrashed()->with([
            'respondentType',
            'survey',
            'employeeService',
            'employeeService.employee',
            'employeeService.service',
        ])->join('surveys', 'answers.survey_id', '=', 'surveys.id')
            ->select('answers.*')
            ->orderBy('answers.created_at', 'desc')
            ->orderBy('surveys.version', 'desc');

        $user = auth()->user();

        // Apply role-based filters using proper relationship constraints
        if ($user->hasRole(UserRole::CampusCoordinator)) {
            $query->whereHas('employeeService.employee', function ($query) use ($user) {
                $query->where('campus_id', $user->campus_id);
            });
        }

        if ($user->hasRole(UserRole::ProcessLeader)) {
            $query->whereHas('employeeService.employee', function ($query) use ($user) {
                $query->where('campus_id', $user->campus_id);
            });
            $query->whereHas('employeeService.service', function ($query) use ($user) {
                $query->where('process_id', $user->employee()->first()->process_id);
            });
        }

        return QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::exact('survey_id'),
                AllowedFilter::exact('respondent_type_id'),
                AllowedFilter::exact('employeeService.service_id'),
                AllowedFilter::exact('employeeService.employee.campus_id'),
                AllowedFilter::exact('employeeService.employee.process_id'),
                'email',
                AllowedFilter::scope('before', 'date_before'),
                AllowedFilter::scope('after', 'date_after'),
                AllowedFilter::scope('result'),
            ])
            ->allowedSorts(['created_at'])
            ->allowedIncludes(['survey', 'employeeService'])
            ->get();
    }

    public function delete(Answer $answer)
    {
        $answer->delete();
    }

    public function createAnswers(AnswerSurveyRequestDto $requestDto)
    {
        if ($requestDto->version !== $this->surveyService->getCurrentSurvey()->version) {
            throw new BadRequestHttpException('Survey version is not valid');
        }
        $respondentType = $this->respondentTypeService->getRespondentTypeById($requestDto->respondent_type_id);

        $currentSurvey = $this->surveyService->getCurrentSurvey();
        $answers = $requestDto->answers;
        $employeeService = EmployeeServiceModel::query()->findOrFail($requestDto->employee_service_id);
        $currentQuestionsCount = $currentSurvey->questions
            ->filter(function ($question) use ($employeeService) {
                return $question->service_id === $employeeService->service_id ||
                    $question->service_id === null;
            })
            ->count();
        if ($currentQuestionsCount !== count($answers)) {
            throw new BadRequestHttpException('All questions must be answered');
        }
        $average = array_sum(array_column($answers, 'answer')) / count($answers);
        $answer = Answer::query()->create([
            'email' => $requestDto->email,
            'respondent_type_id' => $respondentType->id,
            'survey_id' => $currentSurvey->id,
            'employee_service_id' => $requestDto->employee_service_id,
            'average' => $average
        ]);

        foreach ($answers as $answerQuestion) {
            $answer->answerQuestions()->create([
                'question_id' => $answerQuestion->question_id,
                'answer' => $answerQuestion->answer
            ]);
        }
        event(new SurveyCompletion($answer));
    }
}
