<?php

namespace App\DTOs\Profile;

class PendingProfileChangeDto
{
    public function __construct(
        public int $user_id,
        public string $change_type, // 'campus', 'process', 'services'
        public array $new_value,    // e.g., ['campus_id' => 2], ['process_id' => 3], ['services' => [1,2,3]]
        public int $requested_by
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            user_id: $request->user_id,
            change_type: $request->change_type,
            new_value: $request->new_value,
            requested_by: $request->user()->id
        );
    }
}
