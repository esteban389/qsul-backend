<?php

namespace App\DTOs\Survey;

use App\DTOs\DataTransferObject;

class CreateRespondentTypeRequestDto implements DataTransferObject
{

    public function __construct(
        public string $name,
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function fromRequest($request): self
    {
        return new self(
            name: $request->validated(['name']),
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
