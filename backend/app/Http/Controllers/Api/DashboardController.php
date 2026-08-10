<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DisasterType;
use App\Models\Farmer;
use App\Models\Province;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Summary cards shown at the top of the dashboard:
     * provinces at risk, districts receiving help, farmers helped,
     * damaged area, budget disbursed, staff deployed, etc.
     */
    public function summary(): JsonResponse
    {
        $riskyProvinces = Province::whereIn('risk_level', ['high', 'critical'])->count();
        $amphoeAffected = Province::sum('amphoe_affected');
        $farmersAffected = Province::sum('farmers_affected');
        $damagedAreaRai = Province::sum('damaged_area_rai');

        $totalAssistance = Farmer::sum('assistance_amount');
        $paidCount = Farmer::where('assistance_paid', true)->count();
        $totalFarmers = max(Farmer::count(), 1);
        $paidPercent = round(($paidCount / $totalFarmers) * 100);

        return response()->json([
            'provinces_at_risk' => $riskyProvinces,
            'amphoe_affected' => (int) $amphoeAffected,
            'farmers_affected' => (int) $farmersAffected,
            'damaged_area_million_rai' => round($damagedAreaRai / 1000000, 2),
            'assistance_disbursed' => (float) $totalAssistance,
            'assistance_progress_percent' => $paidPercent,
            'staff_deployed' => 2416, // demo static value, wire to a real table if available
            'report_time' => now()->format('H:i'),
        ]);
    }

    /**
     * Gauge values for the four disaster categories
     * (drought / flood / pest / cold) shown on the dashboard.
     */
    public function disasterGauges(): JsonResponse
    {
        $types = DisasterType::select('code', 'name_th', 'icon', 'severity_percent', 'severity_label')
            ->get();

        return response()->json($types);
    }
}
