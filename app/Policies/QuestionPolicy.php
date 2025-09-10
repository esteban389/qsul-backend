<?php

namespace App\Policies;

use App\DTOs\Auth\UserRole;
use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
      public function create(User $user): bool
    {
        return $user->hasRole(UserRole::NationalCoordinator);
    }

    public function delete(User $user, Question $question): bool
    {
        if(!$user->hasRole(UserRole::NationalCoordinator)){
            return false;
        }

        if(!$question->service()->exists()){
            return false;
        }

        return true;
    }

    public function update(User $user, Question $question): bool
    {
        if(!$user->hasRole(UserRole::NationalCoordinator)){
            return false;
        }

        if(!$question->service()->exists()){
            return false;
        }

        return true;
    }
}
