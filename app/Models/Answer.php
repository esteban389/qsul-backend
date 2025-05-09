<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use OwenIt\Auditing\Auditable as AuditingAuditable;
use OwenIt\Auditing\Contracts\Auditable;

class Answer extends Model implements Auditable
{

    use HasFactory, SoftDeletes, AuditingAuditable;

    protected $fillable = [
        'employee_service_id',
        'survey_id',
        'average',
        'email',
        'respondent_type_id',
        'answer_observation_id',
        'solved_at',
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

    public function answerQuestions()
    {
        return $this->hasMany(AnswerQuestion::class);
    }

    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class);
    }

    public function answerObservation(): BelongsTo
    {
        return $this->belongsTo(AnswerObservation::class);
    }

    public function scopeDateBefore($query, $date)
    {
        return $query->where('created_at', '<=', Carbon::parse($date));
    }

    public function scopeDateAfter($query, $date)
    {
        return $query->where('created_at', '>=', Carbon::parse($date));
    }

    public function scopeResult($query, $result)
    {
        return match ($result) {
            'good' => $query->where('average', '>=', 4),
            'sufficent' => $query->where('average', '>=', 3)->where('average', '<', 4),
            default => $query->where('average', '<', 3),
        };
    }

    /**
     * {@inheritdoc}
     */
    public function auditable()
    {
        return $this->morphTo()->withTrashed();
    }
}
