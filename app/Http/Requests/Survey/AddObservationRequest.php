<?php

namespace App\Http\Requests\Survey;

use App\DTOs\Survey\ObservationType;
use App\Models\Observation;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AddObservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return Gate::authorize('create', [Observation::class, $this->answer]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'description' => ['required','string'],
            'type' => ['required','string', Rule::enum(ObservationType::class)],
        ];
    }
}
