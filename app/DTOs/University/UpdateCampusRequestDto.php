<?php

namespace App\DTOs\University;

use App\DTOs\DataTransferObject;
use Illuminate\Http\UploadedFile;

readonly class UpdateCampusRequestDto implements DataTransferObject
{

    public function __construct(
        public string $name,
        public string $address,
        public UploadedFile $icon,
    ){}
    public static function fromRequest($request): UpdateCampusRequestDto
    {
        return new self(
            name: $request->validated('name'),
            address: $request->validated('address'),
            icon: $request->validated('icon'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
            'icon' => $this->icon,
        ];
    }
}
