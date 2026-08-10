<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'message', 'risk_level', 'watch_provinces',
        'issued_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'watch_provinces' => 'array',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
