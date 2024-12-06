<?php

namespace App\DTOs\University;

use App\DTOs\DataTransferObject;
use Illuminate\Http\UploadedFile;

readonly class UpdateServiceRequestDto implements DataTransferObject
{
    public function __construct(
        public ?string $name = null,
        public ?UploadedFile $icon = null,
    )
    {
    }
    public static function fromRequest($request):UpdateServiceRequestDto
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
