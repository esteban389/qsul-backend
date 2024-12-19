<?php

namespace App\DTOs\Survey;

use App\DTOs\DataTransferObject;

readonly class CreateSurveyRequestDto implements DataTransferObject
{

    /**
     * @var QuestionsDto[] $questions
     */
    public array $questions;
    public ?bool $keep_service_questions;

    public function __construct(array $questions, ?bool $keep_service_questions = null)
    {
        $this->questions = $questions;
        $this->keep_service_questions = $keep_service_questions;
    }

    public static function fromRequest($request): self
    {
        return new self(
            array_map(
                fn($question) => new QuestionsDto(
                    $question['text'],
                    $question['type'],
                    $question['order']
                ),
                $request->validated(['questions'])
            ),
            $request->validated(['keep_service_questions'])
        );
    }

    public function toArray(): array
    {
        return [
            'questions' => array_map(
                fn($question) => $question->toArray(),
                $this->questions
            ),
            'keep_service_questions' => $this->keep_service_questions
        ];
    }
}
