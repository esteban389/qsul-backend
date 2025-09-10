<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditingAuditable;
use OwenIt\Auditing\Contracts\Auditable;

class RespondentType extends Model implements Auditable
{
    use SoftDeletes, HasFactory, AuditingAuditable;

    protected $fillable = [
        'name'
    ];
}
