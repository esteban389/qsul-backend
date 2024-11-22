<?php

namespace App\DTOs\University;

use App\DTOs\DataTransferObject;
use Illuminate\Http\UploadedFile;

readonly class UpdateProcessRequestDto implements DataTransferObject
{
    public function __construct(
        public ?string $name = null,
        public ?UploadedFile $icon = null,
        public ?int $parent_id = null,
    )
    {
    }

    public static function fromRequest($request): DataTransferObject
    {
        return new self(
            name: $request->validated('name'),
            icon: $request->validated('icon'),
            parent_id: $request->validated('parent_id'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'icon' => $this->icon,
            'parent_id' => $this->parent_id,
        ];
    }
}
