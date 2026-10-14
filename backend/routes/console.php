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

