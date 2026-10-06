<?php

namespace App\Services;

use App\Models\ApiCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service สำหรับดึงข้อมูลจาก API ต้นทางภายนอก (Thai Water, TMD, eFarmer ฯลฯ)
 * 
 * หลักการทำงาน (DB-First & Time Series):
 * 1. ตรวจสอบข้อมูลในฐานข้อมูล (DB) ก่อนเสมอ
 * 2. ถ้าใน DB มีข้อมูลอยู่แล้ว -> ดึงข้อมูลจาก DB ส่งให้ Frontend ทันที (รวดเร็วและไม่พึ่งพาเน็ตภายนอก)
 * 3. ถ้าใน DB ยังไม่มีข้อมูล (หรือขึ้นรอบวันใหม่) -> เรียก API จากต้นทาง นำมาบันทึกลง DB เป็น Time Series
 * 4. หาก API ต้นทางล่มหรือ Timeout -> ใช้ข้อมูลเดิมที่มีใน DB สำรองตอบกลับเสมอ
 */
class ExternalApiService
{
    /**
     * Standardized HTTP client with browser headers, timeout, and SSL bypass.
     */
    protected function httpGet(string $url, int $timeout = 60): mixed
    {
        $response = Http::timeout($timeout)
            ->connectTimeout(15)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Accept' => 'application/json, text/plain, */*',
            ])
            ->withoutVerifying()
            ->get($url);

        if (!$response->successful()) {
            throw new \Exception("External API returned HTTP {$response->status()} for URL: {$url}");
        }

        return $response->json();
    }

    /**
     * Generic DB-First resolver:
     * ตรวจสอบว่าใน DB มีข้อมูลหรือไม่ ถ้ามีแล้วให้ดึงจาก DB
     * ถ้ายังไม่มี จึงเรียก API ต้นทางแล้วนำมาเก็บลง DB
     *
     * @param string $endpoint Cache key in DB
     * @param callable $fetcher Upstream HTTP request closure
     * @param int|null $ttlSeconds อายุของข้อมูล (วินาที) ถ้า null จะดึงจาก DB เสมอตราบเท่าที่มีข้อมูล
     * @param bool $force บังคับดึงข้อมูลใหม่จากต้นทาง
     */
    public function fetch(string $endpoint, callable $fetcher, ?int $ttlSeconds = null, bool $force = false): mixed
    {
        $now = now();
        $cached = null;

        try {
            $cached = ApiCache::where('endpoint', $endpoint)->first();
        } catch (\Throwable $e) {
            Log::warning("ApiCache DB read error for {$endpoint}: " . $e->getMessage());
        }

        // ตรวจสอบว่าใน DB มีข้อมูลอยู่แล้วหรือไม่
        $hasDataInDb = $cached && !empty($cached->payload);

        // ถ้ามีข้อมูลใน DB แล้ว และไม่ได้สั่ง force:
        if (!$force && $hasDataInDb) {
            // กรณีไม่มี TTL (เช่น ข้อมูลประจำวัน/ประวัติศาสตร์) หรือข้อมูลยังไม่หมดอายุ: ดึงจาก DB ทันที
            if ($ttlSeconds === null || ($cached->fetched_at && ($cached->fetched_at->getTimestamp() + $ttlSeconds) > $now->getTimestamp())) {
                return $cached->payload;
            }
        }

        // ถ้าใน DB ตรวจแล้วยังไม่มีข้อมูล (หรือสั่ง force=true หรือหมดอายุรอบใหม่):
        try {
            $payload = $fetcher();
        } catch (\Throwable $e) {
            Log::error("Failed fetching external API for {$endpoint}: " . $e->getMessage());

            // หากเรียกต้นทางไม่สำเร็จ แต่ใน DB เคยมีข้อมูลเดิม -> ให้ดึงข้อมูลเดิมใน DB ตอบกลับเสมอ
            if ($hasDataInDb) {
                return $cached->payload;
            }

            return [
                'result' => 'FAILED',
                'message' => 'Upstream service unavailable: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        // นำข้อมูลที่ได้มาบันทึกเก็บไว้ใน DB
        if ($payload !== null) {
            try {
                if ($cached) {
                    $cached->update(['payload' => $payload, 'fetched_at' => $now]);
                } else {
                    ApiCache::create(['endpoint' => $endpoint, 'payload' => $payload, 'fetched_at' => $now]);
                }
            } catch (\Throwable $e) {
                Log::error("Failed saving ApiCache for {$endpoint}: " . $e->getMessage());
            }
        }

        return $payload;
    }

    // ------------------------------------------------------------------
    // Thai Water API (Rainfall Time Series & Dams)
    // ------------------------------------------------------------------

    /**
     * ข้อมูลปริมาณฝนเมื่อวาน (Time Series รายวัน)
     * - ตรวจว่าใน DB มีข้อมูลของเมื่อวานแล้วหรือยัง
     * - ถ้ามีแล้ว -> ดึงจาก DB ตอบกลับทันที 100% ไม่ต้องยิง API ภายนอกซ้ำ
     * - ถ้ายังไม่มี -> ดึงจาก Thai Water บันทึกเป็น Time Series เก็บใน DB ทั้ง endpoint ปัจจุบันและ snapshot ตามวันที่
     */
    public function rainYesterday(bool $force = false, ?string $date = null): mixed
    {
        $yesterdayDate = $date ?: now()->subDay()->format('Y-m-d');
        $timeSeriesKey = "thaiwater:rain:history:{$yesterdayDate}";
        $endpoint = 'thaiwater:rain/yesterday';

        // 1. ตรวจสอบใน DB ก่อนว่ามีข้อมูลของวันดังกล่าวแล้วหรือไม่
        $cachedHistory = ApiCache::where('endpoint', $timeSeriesKey)->whereNotNull('payload')->first();
        if (!$force && $cachedHistory && !empty($cachedHistory->payload)) {
            return $cachedHistory->payload;
        }

        $cachedEndpoint = ApiCache::where('endpoint', $endpoint)->whereNotNull('payload')->first();
        // ถ้าถูก fetch มาในวันนี้แล้ว ถือว่ามีข้อมูลของรอบเมื่อวานสมบูรณ์แล้ว -> ดึงจาก DB ทันที
        if (!$force && $cachedEndpoint && !empty($cachedEndpoint->payload) && $cachedEndpoint->fetched_at?->isToday()) {
            return $cachedEndpoint->payload;
        }

        // 2. ถ้าใน DB ตรวจแล้วยังไม่มีข้อมูล -> เรียก API ต้นทาง
        $url = 'https://api-v3.thaiwater.net/api/v1/thaiwater30/public/rain_yesterday';
        
        try {
            $payload = $this->httpGet($url, 90);
        } catch (\Throwable $e) {
            Log::error("Failed fetching rainYesterday: " . $e->getMessage());

            // สำรอง: ถ้ามีข้อมูลเดิมใน DB ให้ใช้แทนทันที
            if ($cachedEndpoint && !empty($cachedEndpoint->payload)) {
                return $cachedEndpoint->payload;
            }

            return ['result' => 'FAILED', 'data' => []];
        }

        // 3. บันทึกข้อมูลที่ได้ลง DB เป็น Time Series Snapshot และ Endpoint หลัก
        if (!empty($payload) && is_array($payload) && ($payload['result'] ?? '') === 'OK') {
            $now = now();
            ApiCache::updateOrCreate(
                ['endpoint' => $endpoint],
                ['payload' => $payload, 'fetched_at' => $now]
            );
            ApiCache::updateOrCreate(
                ['endpoint' => $timeSeriesKey],
                ['payload' => $payload, 'fetched_at' => $now]
            );
        }

        return $payload;
    }

    /**
     * ข้อมูลปริมาณฝนวันนี้
     * - ตรวจใน DB ถ้ามีข้อมูลแล้วดึงจาก DB
     * - อัปเดต snapshot เป็น Time Series รายวัน
     */
    public function rainToday(bool $force = false): mixed
    {
        $todayDate = now()->format('Y-m-d');
        $timeSeriesKey = "thaiwater:rain:history:{$todayDate}";
        $url = 'https://api-v3.thaiwater.net/api/v1/thaiwater30/public/rain_today';

        // ตรวจสอบใน DB ก่อน (อายุแคช 10 นาทีสำหรับข้อมูลระหว่างวัน)
        $payload = $this->fetch('thaiwater:rain/today', fn() => $this->httpGet($url, 60), 600, $force);

        // บันทึก snapshot ของวันปัจจุบันลง time series ด้วย
        if (!empty($payload) && is_array($payload) && ($payload['result'] ?? '') === 'OK') {
            ApiCache::updateOrCreate(
                ['endpoint' => $timeSeriesKey],
                ['payload' => $payload, 'fetched_at' => now()]
            );
        }

        return $payload;
    }

    public function rain24h(bool $force = false): mixed
    {
        $url = 'https://api-v3.thaiwater.net/api/v1/thaiwater30/public/rain_24h';

        return $this->fetch('thaiwater:rain/24h', fn() => $this->httpGet($url, 60), 600, $force);
    }

    public function rain3d(bool $force = false): mixed
    {
        $url = 'https://api-v3.thaiwater.net/api/v1/thaiwater30/provinces/rain3d';

        return $this->fetch('thaiwater:rain/3d', fn() => $this->httpGet($url, 60), 1800, $force);
    }

    public function rain7d(bool $force = false): mixed
    {
        $url = 'https://api-v3.thaiwater.net/api/v1/thaiwater30/provinces/rain7d';

        return $this->fetch('thaiwater:rain/7d', fn() => $this->httpGet($url, 60), 3600, $force);
    }

    public function damWater(bool $force = false): mixed
    {
        $url = 'https://api-v3.thaiwater.net/api/v1/thaiwater30/analyst/dam';

        return $this->fetch('thaiwater:analyst/dam', fn() => $this->httpGet($url, 45), 1800, $force);
    }

    // ------------------------------------------------------------------
    // eFarmer (Disaster Breakdown)
    // ------------------------------------------------------------------

    public function disasterBreakdown(bool $force = false): mixed
    {
        $url = 'https://efarmer.doae.go.th/api/disaster/breakdown';

        return $this->fetch('efarmer:disaster/breakdown', fn() => $this->httpGet($url, 30), 1800, $force);
    }

    // ------------------------------------------------------------------
    // TMD (Thailand Meteorological Department)
    // ------------------------------------------------------------------

    public function temperatureStations(bool $force = false): mixed
    {
        $url = 'https://wxmap.tmd.go.th/api/awsnow';

        return $this->fetch('tmd:awsnow', fn() => $this->httpGet($url, 30), 900, $force);
    }

    public function rainfall(bool $force = false): mixed
    {
        $url = 'https://wxmap.tmd.go.th/api/awsrainfall';

        return $this->fetch('tmd:awsrainfall', fn() => $this->httpGet($url, 30), 600, $force);
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

        return $this->fetch('open-meteo:forecast:' . $coords, fn() => $this->httpGet($url, 30), 3600, $force);
    }

    public function reverseGeocode(float $latitude, float $longitude, bool $force = false): mixed
    {
        $coords = sprintf('%.6f,%.6f', $latitude, $longitude);
        $url = sprintf(
            'https://nominatim.openstreetmap.org/reverse?lat=%s&lon=%s&format=jsonv2&addressdetails=1&zoom=14&accept-language=th',
            $latitude,
            $longitude
        );

        return $this->fetch('nominatim:reverse:' . $coords, fn() => $this->httpGet($url, 30), 86400, $force);
    }

    /**
     * ดึงรายการวันที่ที่บันทึกข้อมูล Time Series ไว้ในระบบ
     * @return array<string>
     */
    public function getAvailableRainHistoryDates(): array
    {
        return ApiCache::where('endpoint', 'like', 'thaiwater:rain:history:%')
            ->orderBy('endpoint', 'desc')
            ->pluck('endpoint')
            ->map(fn($k) => str_replace('thaiwater:rain:history:', '', $k))
            ->values()
            ->all();
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
