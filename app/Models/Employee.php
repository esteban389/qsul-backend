<?php

namespace App\Models;

use App\Lib\NanoId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

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

    public function process(): HasOne
    {
        return $this->hasOne(Process::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($employee) {
            $employee->token = (new NanoId())->generateId(size: 12);
        });
    }
}
