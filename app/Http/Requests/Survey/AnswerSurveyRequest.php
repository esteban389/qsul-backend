<?php

namespace App\Http\Requests\Survey;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AnswerSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'version' => ['required','integer'],
            'email' => ['required','string','email'],
            'respondent_type_id' => ['required','exists:respondent_types,id'],
            'employee_service_id' => ['required','exists:employee_service,id'],
            'answers' => ['required','array'],
            'answers.*.question_id' => ['required','integer'],
            'answers.*.answer' =>['required','integer'],
            'observation' => ['nullable','string'],
        ];
    }
}
