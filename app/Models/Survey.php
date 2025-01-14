<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditingAuditable;
use OwenIt\Auditing\Contracts\Auditable;

class Survey extends Model implements Auditable
{

    use HasFactory, SoftDeletes, AuditingAuditable;

    protected $fillable = [
        'version'
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
