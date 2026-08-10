<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_th', 'name_en', 'region', 'risk_level', 'lat', 'lng',
        'amphoe_affected', 'tambon_affected', 'farmers_affected',
        'damaged_area_rai', 'data_updated_at',
    ];

    protected $casts = [
        'lat' => 'decimal:6',
        'lng' => 'decimal:6',
        'damaged_area_rai' => 'decimal:2',
        'data_updated_at' => 'datetime',
    ];

    public function disasterReports(): HasMany
    {
        return $this->hasMany(DisasterReport::class);
    }

    public function farmers(): HasMany
    {
        return $this->hasMany(Farmer::class);
    }
}
