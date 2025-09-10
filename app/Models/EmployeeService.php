<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditingAuditable;
use OwenIt\Auditing\Contracts\Auditable;

class EmployeeService extends Pivot implements Auditable
{
    use AuditingAuditable, SoftDeletes;

    protected $table = 'employee_service';
    
    // Make sure the model knows about these attributes
    protected $fillable = ['employee_id', 'service_id'];
    
    // Ensure the ID is properly set for auditing
    public $incrementing = true;

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
