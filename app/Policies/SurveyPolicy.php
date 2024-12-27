<?php

namespace App\Policies;

use App\DTOs\Auth\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SurveyPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::NationalCoordinator);
    }

    public function view(User $user): bool
    {
        return Auth::check();
    }
}
