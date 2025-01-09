<?php

namespace App\DTOs\Survey;

readonly class AnswerDto
{

    public function __construct(
        public int $question_id,
        public int $answer,
    )
    {
    }

    public function toArray()
    {
        return [
            'question_id' => $this->question_id,
            'answer' => $this->answer
        ];
    }
}
