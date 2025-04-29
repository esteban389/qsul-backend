<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PendingProfileChange;

class PendingProfileChangePolicy
{
    /**
     * Determine if the user can approve a pending profile change.
     */
    public function approve(User $user, PendingProfileChange $change): bool
    {
        if ($change->change_type === 'campus') {
            return $user->hasRole(\App\DTOs\Auth\UserRole::NationalCoordinator);
        } elseif (in_array($change->change_type, ['process','services'])) {
            return $user->hasRole(\App\DTOs\Auth\UserRole::CampusCoordinator)
                && $change->user->campus_id === $user->campus_id;
        }
        return false;
    }

    /**
     * Determine if the user can view a pending profile change (for listing requests made by the user)
     */
    public function view(User $user, PendingProfileChange $change): bool
    {
        return $user->id === $change->user_id || $this->approve($user, $change);
    }
}
