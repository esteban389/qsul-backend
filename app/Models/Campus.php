<?php

namespace App\Models;

use App\Lib\NanoId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditingAuditable;
use OwenIt\Auditing\Contracts\Auditable;

class Campus extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditingAuditable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'address', 'icon'];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            $employee->token = (new NanoId())->generateId(size: 12);
        });
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
