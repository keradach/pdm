<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Proxy endpoints for third-party APIs. Every response is (re)fetched by
 * ExternalApiService and persisted into the `api_caches` table, so the
 * frontend reads from the DB rather than calling upstream services directly.
 */
class ExternalApiController extends Controller
{
    public function __construct(protected ExternalApiService $external) {}

    // eFarmer
    public function disasterBreakdown(): JsonResponse
    {
        return response()->json($this->external->disasterBreakdown());
    }

    // eFarmer — plant disaster subsidy summary
    public function disasterSubsidy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => ['required', 'date_format:Y-m'],
            'date_to' => ['required', 'date_format:Y-m', 'after_or_equal:date_from'],
            'danger_type_id' => ['nullable', 'integer'],
            'danger_type_name' => ['nullable', 'string', 'max:255'],
            'province_code' => ['nullable', 'string', 'max:10'],
            'amphur_code' => ['nullable', 'string', 'max:10'],
            'tambon_code' => ['nullable', 'string', 'max:10'],
        ]);

        foreach ([
            'danger_type_id',
            'danger_type_name',
            'province_code',
            'amphur_code',
            'tambon_code',
        ] as $optionalFilter) {
            $validated[$optionalFilter] = $validated[$optionalFilter] ?? null;
        }

        $result = $this->external->disasterSubsidy($validated);

        if (is_array($result) && ($result['result'] ?? null) === 'FAILED') {
            return response()->json($result, 502);
        }

        return response()->json($result);
    }

    // Thai Water API
    public function rainToday(Request $request): JsonResponse
    {
        $force = $request->boolean('force', false);
        return response()->json($this->external->rainToday($force));
    }

    public function rainYesterday(Request $request): JsonResponse
    {
        $force = $request->boolean('force', false);
        $date = $request->query('date');
        return response()->json($this->external->rainYesterday($force, $date));
    }

    public function rainHistory(): JsonResponse
    {
        return response()->json([
            'result' => 'OK',
            'available_dates' => $this->external->getAvailableRainHistoryDates(),
        ]);
    }

    public function rain24h(): JsonResponse
    {
        return response()->json($this->external->rain24h());
    }

    public function rain3d(): JsonResponse
    {
        return response()->json($this->external->rain3d());
    }

    public function rain7d(): JsonResponse
    {
        return response()->json($this->external->rain7d());
    }

    public function damWater(): JsonResponse
    {
        return response()->json($this->external->damWater());
    }

    // TMD API
    public function temperatureStations(): JsonResponse
    {
        return response()->json($this->external->temperatureStations());
    }

    public function rainfall(): JsonResponse
    {
        return response()->json($this->external->rainfall());
    }

    // Open-Meteo
    public function weatherForecast(Request $request): JsonResponse
    {
        $lat = (float) $request->query('lat', 13.75);
        $lng = (float) $request->query('lng', 100.5);

        return response()->json($this->external->weatherForecast($lat, $lng));
    }

    // Nominatim
    public function reverseGeocode(Request $request): JsonResponse
    {
        $lat = (float) $request->query('lat', 13.75);
        $lng = (float) $request->query('lng', 100.5);

        return response()->json($this->external->reverseGeocode($lat, $lng));
    }

    // efarmer.doae.go.th — พื้นที่ยังไม่เก็บเกี่ยว
    public function noneProduce(Request $request): JsonResponse
    {
        $level        = $request->input('level', 'province');
        $areaCode     = $request->input('areaCode', null);
        $dateDisaster = $request->input('dateDisaster');
        $force        = $request->boolean('force', false);

        return response()->json($this->external->noneProduce($level, $areaCode, $dateDisaster, $force));
    }

    // riskmap.doae.go.th — ปริมาณน้ำฝนเฉลี่ย 24 ชม.
    public function rainAverage(Request $request): JsonResponse
    {
        $level     = $request->query('level', 'p');
        $adminCode = $request->query('admin_code');
        $force     = $request->boolean('force', false);

        return response()->json($this->external->rainAverage($level, $adminCode, $force));
    }

    // คาดการณ์ความเสี่ยงภัยพิบัติล่วงหน้า (Early Warning Disaster Forecast)
    public function disasterForecast(Request $request): JsonResponse
    {
        $horizon  = $request->query('horizon', '3d');
        $level    = $request->query('level', 'p');
        $areaCode = $request->query('area_code', $request->query('province_code', $request->query('district_code')));
        $force    = $request->boolean('force', false);

        return response()->json($this->external->disasterForecast($horizon, $level, $areaCode, $force));
    }
}
