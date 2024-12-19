<?php

namespace App\DTOs\Survey;

readonly class QuestionsDto
{

    public function __construct(
        public string $text,
        public string $type,
        public string $order,
        public ?int $service_id = null
    )
    {
    }

    public function toArray()
    {
        return [
            'text' => $this->text,
            'type' => $this->type,
            'order' => $this->order,
            'service_id' => $this->service_id,
        ];
    }
}
