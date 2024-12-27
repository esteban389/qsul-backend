<?php

namespace App\DTOs\Survey;

use App\DTOs\DataTransferObject;

readonly class CreateServiceQuestionRequestDto implements DataTransferObject
{

    public function __construct(
        public string $text,
        public string $type,
        public string $order,
    )
    {
    }

    public static function fromRequest($request): self
    {
        return new self(
            $request->validated(['text']),
            $request->validated(['type']),
            $request->validated(['order']),
        );
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'type' => $this->type,
            'order' => $this->order,
        ];
    }
}
