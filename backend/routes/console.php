<?php

use App\Services\ExternalApiService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('api:cache', function ($force = false) {
    $service = new ExternalApiService();

    $service->disasterBreakdown($force);
    $service->rainToday($force);
    $service->rainYesterday($force);
    $service->rain24h($force);
    $service->rain3d($force);
    $service->rain7d($force);
    $service->damWater($force);
    $service->temperatureStations($force);
    $service->rainfall($force);

    $this->comment('Done: third-party API responses persisted into api_caches.');
})->purpose('Fetch all third-party APIs and persist their responses into the database');
