<?php

namespace App\Models;

use App\Lib\NanoId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Process extends Model
{
    use HasFactory, SoftDeletes;

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
