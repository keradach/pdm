<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisasterReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'province_id', 'disaster_type_id', 'report_month',
        'damaged_area_rai', 'farmers_affected', 'status',
    ];

    protected $casts = [
        'report_month' => 'date',
        'damaged_area_rai' => 'decimal:2',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function disasterType(): BelongsTo
    {
        return $this->belongsTo(DisasterType::class);
    }
}
