<?php

namespace App\Http\Requests\Auth;

use App\DTOs\Auth\UserRole;
use App\Models\Campus;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Represents a create user request.
 * @package App\Http\Requests\Auth
 */
class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request (basic check).
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role!==UserRole::ProcessLeader;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'avatar' => ['required', 'image', 'max:2048'],
            'campus_id' => ['nullable','integer','exists:'.Campus::class.',id'],
        ];
    }
}
