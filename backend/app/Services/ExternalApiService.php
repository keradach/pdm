<?php

namespace App\Services;

use App\Models\ApiCache;
use Illuminate\Support\Facades\Http;

/**
 * Fetch third-party APIs while persisting every raw response into the
 * `api_caches` table. The frontend only ever talks to the Laravel proxy
 * endpoints (which read from this table), so data comes from the DB first
 * and the upstream API is only hit once its cache has expired (TTL).
 *
 * If an upstream call fails but we have a stale DB copy, the stale copy is
 * returned instead of erroring out.
 */
class ExternalApiService
{
    /**
     * Resolve an endpoint from the database cache, falling back to a live
     * upstream fetch that is then persisted to the database.
     *
     * @param  string    $endpoint   unique cache key (e.g. "thaiwater:rain/today")
     * @param  callable  $fetcher    closure that performs the upstream request
     * @param  int       $ttlSeconds how long a DB copy is considered fresh
     * @param  bool      $force      ignore TTL and always re-fetch
     * @return mixed                 the JSON payload (array / scalar / string)
     */
    public function fetch(string $endpoint, callable $fetcher, int $ttlSeconds = 300, bool $force = false): mixed
    {
        $now = now();
        $cached = ApiCache::where('endpoint', $endpoint)->first();

        if (
            ! $force && $cached && $cached->fetched_at &&
            ($cached->fetched_at->getTimestamp() + $ttlSeconds) > $now->getTimestamp()
        ) {
            return $cached->payload;
        }

        try {
            $payload = $fetcher();
        } catch (\Throwable $e) {
            // Upstream unreachable → keep the last DB copy (best effort).
            if ($cached && $cached->fetched_at) {
                return $cached->payload;
            }

            throw $e;
        }

        if ($cached) {
            $cached->update(['payload' => $payload, 'fetched_at' => $now]);
        } else {
            ApiCache::create(['endpoint' => $endpoint, 'payload' => $payload, 'fetched_at' => $now]);
        }

        return $payload;
    }

    // ------------------------------------------------------------------
    // eFarmer
    // ------------------------------------------------------------------

    public function disasterBreakdown(bool $force = false): mixed
    {
        $url = 'https://efarmer.doae.go.th/api/disaster/breakdown';

        return $this->fetch('efarmer:disaster/breakdown', fn() => Http::get($url)->json(), 900, $force);
    }

    // ------------------------------------------------------------------
    // Thai Water API (rainfall / dam)
    // ------------------------------------------------------------------

    public function rainToday(bool $force = false): mixed
    {
        $url = 'https://api-v3.thaiwater.net/api/v1/thaiwater30/public/rain_today';

        return $this->fetch('thaiwater:rain/today', fn() => Http::get($url)->json(), 300, $force);
    }

    public function rainYesterday(bool $force = false): mixed
    {
        $url = 'https://api-v3.thaiwater.net/api/v1/thaiwater30/public/rain_yesterday';

        return $this->fetch('thaiwater:rain/yesterday', fn() => Http::get($url)->json(), 1800, $force);
    }

    public function rain24h(bool $force = false): mixed
    {
        $url = 'https://api-v3.thaiwater.net/api/v1/thaiwater30/public/rain_24h';

        return $this->fetch('thaiwater:rain/24h', fn() => Http::get($url)->json(), 300, $force);
    }

    public function rain3d(bool $force = false): mixed
    {
        $url = 'https://api-v3.thaiwater.net/api/v1/thaiwater30/provinces/rain3d';

        return $this->fetch('thaiwater:rain/3d', fn() => Http::get($url)->json(), 1800, $force);
    }

    public function rain7d(bool $force = false): mixed
    {
        $url = 'https://api-v3.thaiwater.net/api/v1/thaiwater30/provinces/rain7d';

        return $this->fetch('thaiwater:rain/7d', fn() => Http::get($url)->json(), 3600, $force);
    }

    public function damWater(bool $force = false): mixed
    {
        $url = 'https://api-v3.thaiwater.net/api/v1/thaiwater30/analyst/dam';

        return $this->fetch('thaiwater:analyst/dam', fn() => Http::get($url)->json(), 1800, $force);
    }
    // ------------------------------------------------------------------
    // TMD (Thailand Meteorological Department)
    // ------------------------------------------------------------------

    public function temperatureStations(bool $force = false): mixed
    {
        $url = 'https://wxmap.tmd.go.th/api/awsnow';

        return $this->fetch('tmd:awsnow', fn() => Http::get($url)->json(), 900, $force);
    }

    public function rainfall(bool $force = false): mixed
    {
        $url = 'https://wxmap.tmd.go.th/api/awsrainfall';

        return $this->fetch('tmd:awsrainfall', fn() => Http::get($url)->json(), 300, $force);
    }

    // ------------------------------------------------------------------
    // Open-Meteo + Nominatim (weather card)
    // ------------------------------------------------------------------

    public function weatherForecast(float $latitude, float $longitude, bool $force = false): mixed
    {
        $coords = sprintf('%.6f,%.6f', $latitude, $longitude);
        $url = sprintf(
            'https://api.open-meteo.com/v1/forecast?latitude=%s&longitude=%s&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max&timezone=Asia/Bangkok&forecast_days=7',
            $latitude,
            $longitude
        );

        return $this->fetch('open-meteo:forecast:' . $coords, fn() => Http::get($url)->json(), 3600, $force);
    }

    public function reverseGeocode(float $latitude, float $longitude, bool $force = false): mixed
    {
        $coords = sprintf('%.6f,%.6f', $latitude, $longitude);
        $url = sprintf(
            'https://nominatim.openstreetmap.org/reverse?lat=%s&lon=%s&format=jsonv2&addressdetails=1&zoom=14&accept-language=th',
            $latitude,
            $longitude
        );

        return $this->fetch('nominatim:reverse:' . $coords, fn() => Http::get($url)->json(), 86400, $force);
    }

    /**
     * @return array<string,string> endpoint → short label for logging
     */
    public function cacheableEndpoints(): array
    {
        return [
            'efarmer:disaster/breakdown' => 'eFarmer',
            'thaiwater:rain/today'       => 'ThaiWater rain_today',
            'thaiwater:rain/yesterday'   => 'ThaiWater rain_yesterday',
            'thaiwater:rain/24h'         => 'ThaiWater rain_24h',
            'thaiwater:rain/3d'          => 'ThaiWater rain3d',
            'thaiwater:rain/7d'          => 'ThaiWater rain7d',
            'thaiwater:analyst/dam'      => 'ThaiWater dam',
            'tmd:awsnow'                 => 'TMD stations',
            'tmd:awsrainfall'            => 'TMD rainfall',
        ];
    }
}
