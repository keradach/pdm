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

    // Thai Water API
    public function rainToday(): JsonResponse
    {
        return response()->json($this->external->rainToday());
    }

    public function rainYesterday(): JsonResponse
    {
        return response()->json($this->external->rainYesterday());
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
}
