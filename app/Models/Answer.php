<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'employee_service_id',
        'survey_id',
        'average',
        'email',
        'respondent_type_id'
    ];

    public function employeeService()
    {
        return $this->belongsTo(EmployeeService::class);
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function respondentType()
    {
        return $this->belongsTo(RespondentType::class);
    }
}
