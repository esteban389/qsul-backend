<?php

namespace App\DTOs\Survey;

use App\DTOs\DataTransferObject;

readonly class AnswerSurveyRequestDto implements DataTransferObject
{

    /**
     * @param int $version
     * @param string $email
     * @param int $respondent_type_id
     * @param int $employee_service_id
     * @param AnswerDto[] $answers
     */
    public function __construct(
        public int    $version,
        public string $email,
        public int    $respondent_type_id,
        public int   $employee_service_id,
        public array  $answers,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public static function fromRequest($request): self
    {
        return new self(
            version: $request->validated(['version']),
            email: $request->validated(['email']),
            respondent_type_id: $request->validated(['respondent_type_id']),
            employee_service_id: $request->validated(['employee_service_id']),
            answers: array_map(fn($answer) => new AnswerDto($answer['question_id'], $answer['answer']), $request->validated(['answers'])),
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'email' => $this->email,
            'respondent_type_id' => $this->respondent_type_id,
            'employee_service_id' => $this->employee_service_id,
            'answers' => array_map(fn($answer) => $answer->toArray(), $this->answers)
        ];
    }
}
