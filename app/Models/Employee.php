<?php

namespace App\Models;

use App\Lib\NanoId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditingAuditable;
use OwenIt\Auditing\Contracts\Auditable;

class Employee extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditingAuditable;

    protected $fillable = [
        'name',
        'avatar',
        'campus_id',
        'email',
        'process_id'
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)
            ->using(EmployeeService::class)
            ->wherePivotNull('deleted_at');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($employee) {
            $employee->token = (new NanoId())->generateId(size: 12);
        });
    }
}
