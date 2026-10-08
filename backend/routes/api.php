<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DisasterReportController;
use App\Http\Controllers\Api\ExternalApiController;
use App\Http\Controllers\Api\ProvinceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - PDM (Plant Disaster Monitoring Platform)
|--------------------------------------------------------------------------
| All routes are prefixed with /api by Laravel's routing (see
| bootstrap/app.php -> withRouting(api: __DIR__.'/../routes/api.php')).
*/

Route::prefix('dashboard')->group(function () {
    Route::get('/summary', [DashboardController::class, 'summary']);
    Route::get('/gauges', [DashboardController::class, 'disasterGauges']);
});

Route::prefix('provinces')->group(function () {
    Route::get('/', [ProvinceController::class, 'index']);
    Route::get('/top-damaged', [ProvinceController::class, 'topDamaged']);
    Route::get('/{province}', [ProvinceController::class, 'show']);
});

Route::prefix('reports')->group(function () {
    Route::get('/trend', [DisasterReportController::class, 'trend']);
    Route::get('/breakdown', [DisasterReportController::class, 'breakdown']);
});

Route::get('/alerts', [AlertController::class, 'active']);

/*
|--------------------------------------------------------------------------
| Third-party API proxies (responses cached into the `api_caches` table).
| The frontend reads from these DB-backed endpoints instead of calling the
| upstream TMD / Thai Water / eFarmer / Open-Meteo / Nominatim services.
|--------------------------------------------------------------------------
*/
Route::prefix('external')->group(function () {
    Route::get('/disaster/breakdown', [ExternalApiController::class, 'disasterBreakdown']);

    Route::get('/rain/today', [ExternalApiController::class, 'rainToday']);
    Route::get('/rain/yesterday', [ExternalApiController::class, 'rainYesterday']);
    Route::get('/rain/history', [ExternalApiController::class, 'rainHistory']);
    Route::get('/rain/24h', [ExternalApiController::class, 'rain24h']);
    Route::get('/rain/3d', [ExternalApiController::class, 'rain3d']);
    Route::get('/rain/7d', [ExternalApiController::class, 'rain7d']);
    Route::get('/dam-water', [ExternalApiController::class, 'damWater']);

    Route::get('/temperature-stations', [ExternalApiController::class, 'temperatureStations']);
    Route::get('/rainfall', [ExternalApiController::class, 'rainfall']);

    Route::get('/weather/forecast', [ExternalApiController::class, 'weatherForecast']);
    Route::get('/weather/reverse-geocode', [ExternalApiController::class, 'reverseGeocode']);

    // efarmer.doae.go.th — พื้นที่ยังไม่เก็บเกี่ยว
    Route::post('/none-produce', [ExternalApiController::class, 'noneProduce']);

    // riskmap.doae.go.th — ปริมาณน้ำฝนเฉลี่ย 24 ชม.
    Route::get('/rain-average', [ExternalApiController::class, 'rainAverage']);
});


/*
|--------------------------------------------------------------------------
| Authentication Routes (Staff)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
