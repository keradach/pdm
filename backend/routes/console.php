<?php

use App\Services\ExternalApiService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('api:cache {--force} {--date=}', function () {
    $force = (bool) $this->option('force');
    $date = $this->option('date') ?: now()->format('Y-m-d');
    $service = new ExternalApiService();

    $this->info("Fetching and caching third-party APIs (force=" . ($force ? 'true' : 'false') . ")...");

    $this->line(" - disasterBreakdown");
    $service->disasterBreakdown($force);

    $this->line(" - rainfall & stations");
    $service->rainToday($force);
    $service->rainYesterday($force);
    $service->rain24h($force);
    $service->rain3d($force);
    $service->rain7d($force);
    $service->damWater($force);
    $service->temperatureStations($force);
    $service->rainfall($force);

    $this->line(" - rainAverage");
    $service->rainAverage('p', null, $force);

    $this->line(" - noneProduce (date: {$date})");
    $service->noneProduce('province', null, $date, $force);

    $this->info('Done: all third-party API responses persisted into api_caches.');
})->purpose('Fetch all third-party APIs and persist their responses into the database');

Artisan::command('api:cache-none-produce {--date=} {--level=province} {--area-code=} {--force}', function () {
    $force = (bool) $this->option('force');
    $level = (string) ($this->option('level') ?: 'province');
    $areaCode = $this->option('area-code');
    $date = $this->option('date') ?: now()->format('Y-m-d');

    $this->info("Fetching noneProduce from efarmer.doae.go.th (level: {$level}, date: {$date}, areaCode: " . ($areaCode ?: 'all') . ", force=" . ($force ? 'true' : 'false') . ")...");

    $service = new ExternalApiService();
    $result = $service->noneProduce($level, $areaCode, $date, $force);

    if (is_array($result) && isset($result['result']) && $result['result'] === 'FAILED') {
        $this->error("Failed: " . ($result['message'] ?? 'Unknown error'));
        return 1;
    }

    $count = is_array($result) ? count($result['data'] ?? $result) : 0;
    $this->info("Success! Fetched and cached noneProduce data ({$count} items).");
    return 0;
})->purpose('Fetch noneProduce from efarmer.doae.go.th and cache into database');

Artisan::command('api:test-efarmer', function () {
    $service = new ExternalApiService();
    $baseUrl = $service->efarmerBaseUrl();
    $resolveIp = config('services.efarmer.resolve_ip', env('EFARMER_RESOLVE_IP'));

    $this->info("=== Testing eFarmer Connectivity ===");
    $this->line("Base URL    : {$baseUrl}");
    $this->line("Resolve IP  : " . ($resolveIp ?: '<not set (using system DNS)>'));

    $host = parse_url($baseUrl, PHP_URL_HOST) ?: 'efarmer.doae.go.th';
    $this->line("Resolving DNS for host: {$host}...");

    $systemIp = @gethostbyname($host);
    if ($systemIp && $systemIp !== $host) {
        $this->info(" -> System DNS resolved to: {$systemIp}");
    } else {
        $this->warn(" -> System DNS FAILED to resolve '{$host}'!");
        if (empty($resolveIp)) {
            $this->error("    Recommendation: Add EFARMER_RESOLVE_IP=<ip> to .env or configure Docker DNS / extra_hosts.");
        }
    }

    $this->line("\nTesting HTTP GET to {$baseUrl}/api/farmer/noneProduce...");
    $startTime = microtime(true);
    try {
        $data = $service->noneProduce(force: true);
        $duration = round(microtime(true) - $startTime, 2);
        $this->info(" -> Success! Status 200 OK (took {$duration}s)");
        if (is_array($data)) {
            $this->line(" -> Response keys: " . implode(', ', array_keys($data)));
        }
    } catch (\Throwable $e) {
        $duration = round(microtime(true) - $startTime, 2);
        $this->error(" -> Failed after {$duration}s: " . $e->getMessage());
    }
})->purpose('Test DNS resolution and HTTP connectivity to efarmer.doae.go.th');

// =============================================================================
// Laravel Scheduler
// =============================================================================
Schedule::command('api:cache-none-produce --force')
    ->everyFourHours()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/schedule.log'));

Schedule::command('api:cache --force')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/schedule.log'));
