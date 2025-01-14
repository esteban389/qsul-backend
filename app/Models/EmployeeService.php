<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditingAuditable;
use OwenIt\Auditing\Contracts\Auditable;

class EmployeeService extends Model implements Auditable
{
    use AuditingAuditable;

    protected $table = 'employee_service';

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function answers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
