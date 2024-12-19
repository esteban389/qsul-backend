<?php

namespace App\Models;

use App\DTOs\Auth\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'survey_id',
        'service_id',
        'text',
        'type',
        'order',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function casts()
    {
        return [
            'type' => QuestionType::class,
        ];
    }
}
