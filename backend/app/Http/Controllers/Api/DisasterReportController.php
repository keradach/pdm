<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DisasterReport;
use Illuminate\Http\JsonResponse;

class DisasterReportController extends Controller
{
    /** Monthly cumulative damaged-area trend (แนวโน้มพื้นที่เสียหาย). */
    public function trend(): JsonResponse
    {
        $rows = DisasterReport::selectRaw('report_month, SUM(damaged_area_rai) as total_rai')
            ->groupBy('report_month')
            ->orderBy('report_month')
            ->get();

        return response()->json($rows);
    }

    /** Breakdown of damaged area by disaster type (สัดส่วนประเภทภัยพิบัติ). */
    public function breakdown(): JsonResponse
    {
        $rows = DisasterReport::join('disaster_types', 'disaster_types.id', '=', 'disaster_reports.disaster_type_id')
            ->selectRaw('disaster_types.name_th as label, SUM(disaster_reports.damaged_area_rai) as total_rai')
            ->groupBy('disaster_types.name_th')
            ->orderByDesc('total_rai')
            ->get();

        return response()->json($rows);
    }
}
