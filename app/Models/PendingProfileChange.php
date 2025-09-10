<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingProfileChange extends Model
{
    protected $fillable = [
        'user_id',
        'change_type',
        'new_value',
        'status',
        'requested_by',
        'approved_by',
        'requested_at',
        'approved_at',
    ];

    protected $casts = [
        'new_value' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
