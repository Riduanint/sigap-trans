<?php

use App\Http\Controllers\Api\UptLocationApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes untuk SIGAP-TRANS WebGIS Kalsel
|--------------------------------------------------------------------------
*/

Route::middleware('web')->group(function () {
    Route::prefix('upt-locations')->group(function () {
        Route::get('/', [UptLocationApiController::class, 'index']);
        Route::get('/{id}', [UptLocationApiController::class, 'show'])->whereNumber('id');
    });

    Route::get('/regencies', [UptLocationApiController::class, 'regencies']);
    Route::get('/regencies/boundaries', [UptLocationApiController::class, 'boundaries']);
    Route::get('/skp-locations', [UptLocationApiController::class, 'skpLocations']);
    Route::get('/skp-locations/cahaya-baru', [UptLocationApiController::class, 'skpCahayaBaru']);
    Route::get('/skp-locations/{id}', [UptLocationApiController::class, 'showSkp']);
    Route::get('/statistics', [UptLocationApiController::class, 'statistics']);
});

