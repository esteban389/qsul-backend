<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class RequestProfileChangeRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'change_type' => 'required|in:campus,process,services',
            'new_value' => 'required|array',
        ];
    }
}
