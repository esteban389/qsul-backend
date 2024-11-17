<?php

namespace App\DTOs\University;

use App\DTOs\DataTransferObject;
use Illuminate\Http\UploadedFile;

readonly class UpdateProcessRequestDto implements DataTransferObject
{
    public function __construct(
        public string $name,
        public UploadedFile $icon,
    )
    {
    }

    public static function fromRequest($request): DataTransferObject
    {
        return new self(
            name: $request->validated('name'),
            icon: $request->validated('icon'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'icon' => $this->icon,
        ];
    }
}
