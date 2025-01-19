<?php

namespace App\Http\Services;

use App\DTOs\Survey\AddObservationRequestDto;
use App\Models\Answer;
use App\Models\Observation;

readonly class ObservationService
{

    public function addObservationToAnswer(AddObservationRequestDto $requestDto, Answer $answer): void
    {
        $user = auth()->user();
        $answer->observations()->create([
            'user_id' => $user->id,
            'description' => $requestDto->description,
            'type' => $requestDto->type,
        ]);
    }

    public function deleteObservation(Observation $observation): void
    {
        $observation->delete();
    }
}
