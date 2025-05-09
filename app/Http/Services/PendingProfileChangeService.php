<?php

namespace App\Http\Services;

use App\DTOs\Profile\PendingProfileChangeDto;
use App\Models\PendingProfileChange;
use App\Models\User;
use App\Notifications\PendingProfileChangeRequested;
use Illuminate\Support\Facades\Notification;

class PendingProfileChangeService
{
    public function createPendingChange(PendingProfileChangeDto $dto): PendingProfileChange
    {
        $json = match($dto->change_type){
            'campus' => json_encode(['campus_id'=>$dto->new_value[0]]),
            'process' => json_encode(['process_id'=>$dto->new_value[0]]),
            'services' => json_encode(['services'=>$dto->new_value]),
        };
        $pending = PendingProfileChange::create([
            'user_id' => $dto->user_id,
            'change_type' => $dto->change_type,
            'new_value' => $json,
            'status' => 'pending',
            'requested_by' => $dto->requested_by,
            'requested_at' => now(),
        ]);

        // Notify the appropriate coordinator(s)
        $user = User::findOrFail($dto->user_id);
        if ($dto->change_type === 'campus') {
            // National Coordinator(s)
            $coordinators = User::where('role', 'national_coordinator')->get();
        } else {
            // Campus Coordinator(s) for the user's current campus
            $coordinators = User::where('role', 'campus_coordinator')
                ->where('campus_id', $user->campus_id)
                ->get();
        }
        Notification::send($coordinators, new PendingProfileChangeRequested($pending));
        return $pending;
    }
}
