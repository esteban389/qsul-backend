<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditingAuditable;
use OwenIt\Auditing\Contracts\Auditable;

class AnswerObservation extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditingAuditable;

    protected $fillable = [
        'observation'
    ];

    public function answer(): HasOne
    {
        return $this->hasOne(Answer::class);
    }
}
