<?php

namespace App\DTOs\University;

use App\DTOs\DataTransferObject;
use Illuminate\Http\UploadedFile;

readonly class CreateServiceRequestDto implements DataTransferObject
{

    public function __construct(
        public string $name,
        public UploadedFile $icon,
        public int $process_id,
    )
    {
    }
    public static function fromRequest($request):CreateServiceRequestDto
    {
        return new self(
            name: $request->validated('name'),
            icon: $request->validated('icon'),
            process_id: $request->validated('process_id'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'icon' => $this->icon,
            'process_id' => $this->process_id,
        ];
    }
}
