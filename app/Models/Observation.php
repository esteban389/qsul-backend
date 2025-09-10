<?php

namespace App\Models;

use App\DTOs\Survey\ObservationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditingAuditable;
use OwenIt\Auditing\Contracts\Auditable;

class Observation extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditingAuditable;

    protected $fillable = [
        'type',
        'description',
        'answer_id',
        'user_id'
    ];

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'type' => ObservationType::class,
    ];
}
