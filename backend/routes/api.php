<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DisasterReportController;
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
