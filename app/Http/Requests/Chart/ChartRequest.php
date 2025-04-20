<?php

namespace App\Http\Requests\Chart;

use Illuminate\Foundation\Http\FormRequest;

class ChartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //return auth()->check();
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'group_by' => 'nullable|string',
            'time_frame' => ['nullable','string','in:month,year'],
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'survey' => 'required|exists:surveys,id',
            'process' => 'nullable|exists:processes,id',
            'campus' => 'nullable|exists:campuses,id',
            'service' => 'nullable|exists:services,id',
            'employee' => 'nullable|exists:employees,id',
        ];
    }
}
