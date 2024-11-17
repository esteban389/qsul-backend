<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'name',
        'icon'
    ];

    public function processes(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }
}
