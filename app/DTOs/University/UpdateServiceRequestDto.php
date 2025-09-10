<?php

namespace App\DTOs\University;

use App\DTOs\DataTransferObject;
use Illuminate\Http\UploadedFile;

readonly class UpdateServiceRequestDto implements DataTransferObject
{
    public function __construct(
        public ?string $name = null,
        public ?UploadedFile $icon = null,
        public ?int $process_id = null,
    )
    {
    }
    public static function fromRequest($request):UpdateServiceRequestDto
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
