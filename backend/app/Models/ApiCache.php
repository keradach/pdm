<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A single cached response (payload + fetched_at) for one third-party API
 * endpoint. Used by ExternalApiService so the frontend reads from the DB
 * instead of hitting upstream services directly.
 */
class ApiCache extends Model
{
    use HasFactory;

    protected $fillable = [
        'endpoint', 'payload', 'fetched_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'fetched_at' => 'datetime',
    ];
}