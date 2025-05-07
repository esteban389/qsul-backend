<?php

namespace App\Models;

use App\Lib\NanoId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditingAuditable;
use OwenIt\Auditing\Contracts\Auditable;

class Service extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditingAuditable;

    protected $fillable = [
        'name',
        'icon',
        'process_id',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class)
            ->using(EmployeeService::class)
            ->wherePivotNull('deleted_at');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($service) {
            $service->token = (new NanoId())->generateId(12);
        });
    }
}
