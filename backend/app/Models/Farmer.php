<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Farmer extends Model
{
    use HasFactory;

    protected $fillable = [
        'national_id', 'full_name', 'province_id', 'amphoe', 'tambon',
        'assistance_amount', 'assistance_paid', 'registered_at',
    ];

    protected $casts = [
        'assistance_amount' => 'decimal:2',
        'assistance_paid' => 'boolean',
        'registered_at' => 'datetime',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
