<?php

namespace App\Models;

use App\Lib\NanoId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditingAuditable;
use OwenIt\Auditing\Contracts\Auditable;

class Process extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditingAuditable;

    protected $fillable = [
        'name',
        'icon',
        'parent_id',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($process) {
            $process->token = (new NanoId())->generateId(size: 12);
        });
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Process::class, 'parent_id','id');
    }

    public function subProcesses(): HasMany
    {
        return $this->hasMany(Process::class, 'parent_id','id');
    }
}
