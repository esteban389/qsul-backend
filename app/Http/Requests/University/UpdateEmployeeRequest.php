<?php

namespace App\Http\Requests\University;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateEmployeeRequest extends FormRequest
{

    public function authorize(): Response
    {
        return Gate::authorize('update', $this->employee);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['string'],
            'email' => ['email'],
            'avatar' => ['image', 'max:2048'],
            'process_id' => ['exists:processes,id'],
        ];
    }
}
