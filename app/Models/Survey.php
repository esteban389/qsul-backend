<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{

    use HasFactory,SoftDeletes;

    protected $fillable = [
        'version'
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
