<?php

namespace App\Http\Services;

use App\DTOs\Survey\CreateServiceQuestionRequestDto;
use App\DTOs\Survey\CreateSurveyRequestDto;
use App\DTOs\Survey\QuestionsDto;
use App\DTOs\Survey\UpdateQuestionRequestDto;
use App\Models\Question;
use App\Models\Service;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Collection;

readonly class QuestionService
{

    public function createSurveyQuestions(CreateSurveyRequestDto $requestDto, Survey $survey): Collection
    {
        $questions = array_map(function (QuestionsDto $question) {
            $question = $question->toArray();
            $question['order'] = $question['service_id'] === null ? 'B'.$question['order'] : 'A'. $question['order'];
            return $question;
        }, $requestDto->questions);
        if($requestDto->keep_service_questions) {
            $previousSurvey = Survey::query()->where('version', $survey->version - 1)->first();
            $serviceQuestions = $previousSurvey->questions()->where('service_id', '!=', null)->get();
            $questions = array_merge($questions, $serviceQuestions->toArray());
        }
        return $survey->questions()->createMany($questions);
    }

    public function deleteQuestion(Question $question): void
    {
        $question->delete();
    }

    public function updateQuestion(Question $question, UpdateQuestionRequestDto $requestDto): void
    {
        $data = array_filter($requestDto->toArray(), fn($value) => $value !== null);
        $question->update($data);
    }

    public function createServiceQuestion(Service $service, CreateServiceQuestionRequestDto $requestDto, Survey $survey): void
    {
        $data = array_merge($requestDto->toArray(), ['survey_id' => $survey->id]);
        $service->questions()->create($data);
    }
}
