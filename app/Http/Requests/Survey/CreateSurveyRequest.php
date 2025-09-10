<?php

namespace App\Http\Requests\Survey;

use App\DTOs\Survey\QuestionType;
use App\Models\Survey;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CreateSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return Gate::authorize('create', Survey::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'questions' => ['required', 'array'],
            'questions.*.text' => ['required', 'string'],
            'questions.*.type' => ['required', Rule::enum(QuestionType::class)],
            'questions.*.order' => ['required', 'integer'],
            'keep_service_questions' => ['boolean'],
        ];
    }
}
