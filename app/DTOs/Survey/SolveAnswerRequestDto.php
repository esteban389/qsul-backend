<?php

namespace App\DTOs\Survey;

use App\DTOs\DataTransferObject;

class SolveAnswerRequestDto implements DataTransferObject
{

    public function __construct(
        public string $observation,
        public string $type,
    ) {
    }

    public static function fromRequest($request): self
    {
        return new self(
            observation: $request->validated('observation'),
            type: $request->validated('type'),
        );
    }

    public function toArray(): array
    {
        return [
            'observation' => $this->observation,
            'type' => $this->type,
        ];
    }
}
