<?php

namespace App\DTOs\Survey;

use App\DTOs\DataTransferObject;

readonly class UpdateQuestionRequestDto implements DataTransferObject
{

    public function __construct(
        public ?string $text = null,
        public ?string $type = null,
        public ?string $order = null,
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
