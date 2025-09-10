<?php

namespace App\DTOs\Survey;

use App\DTOs\DataTransferObject;

readonly class CreateServiceQuestionRequestDto implements DataTransferObject
{

    public function __construct(
        public string $text,
        public string $type,
    )
    {
    }

    public static function fromRequest($request): self
    {
        return new self(
            $request->validated(['text']),
            $request->validated(['type']),
        );
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'type' => $this->type,
        ];
    }
}
