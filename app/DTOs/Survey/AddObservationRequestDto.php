<?php

namespace App\DTOs\Survey;

use App\DTOs\DataTransferObject;

class AddObservationRequestDto implements DataTransferObject
{

    public function __construct(
        public string $description,
        public string $type,
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function fromRequest($request): self
    {
        return new self(
            description: $request->description,
            type: $request->type,
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'type' => $this->type,
        ];
    }
}
