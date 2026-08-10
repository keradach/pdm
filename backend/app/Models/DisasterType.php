<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisasterType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name_th', 'icon', 'severity_percent', 'severity_label',
    ];

    public function disasterReports(): HasMany
    {
        return $this->hasMany(DisasterReport::class);
    }
}
