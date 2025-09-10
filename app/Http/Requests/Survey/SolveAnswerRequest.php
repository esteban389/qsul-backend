<?php

namespace App\Http\Requests\Survey;

use App\DTOs\Survey\ObservationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SolveAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): \Illuminate\Auth\Access\Response
    {
        return Gate::authorize('solve', $this->route('answer'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'observation' => ['required', 'string', 'max:255'],
            'type' => ['required','string', Rule::enum(ObservationType::class)],
        ];
    }
}
