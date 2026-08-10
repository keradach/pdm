<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    /**
     * Full list of provinces with risk level & coordinates, used to
     * render the risk map (แผนที่ความเสี่ยงภัยด้านพืช).
     */
    public function index(): JsonResponse
    {
        return response()->json(
            Province::select(
                'id', 'name_th', 'name_en', 'risk_level', 'lat', 'lng',
                'amphoe_affected', 'tambon_affected', 'farmers_affected', 'damaged_area_rai'
            )->orderBy('name_th')->get()
        );
    }

    /** Detail panel shown when a province on the map is selected. */
    public function show(Province $province): JsonResponse
    {
        return response()->json(
            $province->load(['disasterReports.disasterType'])
        );
    }

    /** Top N provinces by damaged area, for the horizontal bar chart. */
    public function topDamaged(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);

        return response()->json(
            Province::select('name_th', 'damaged_area_rai')
                ->orderByDesc('damaged_area_rai')
                ->limit($limit)
                ->get()
        );
    }
}
