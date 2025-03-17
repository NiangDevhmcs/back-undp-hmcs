<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttemptConnexion extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'email',
        'attempts',
        'last_attempt_at',
        'blocked_until',
        'block_time',
    ];

    protected $casts = [
        'last_attempt_at' => 'datetime',
        'blocked_until' => 'datetime',
    ];
}

