<?php

use App\Http\Controllers\MartApiController;
use App\Http\Controllers\MartFileController;
use Illuminate\Support\Facades\Route;

// MART API data endpoints (mobile-app traffic).
// Higher throttle (300/min/IP) than Laravel's default of 60/min because
// participants poll /structure frequently, submit entries in bursts, and
// upload device-info / stats payloads alongside questionnaires. This
// matches the volume seen during researcher testing and real mobile use.
Route::middleware(['force.json', 'auth:api', 'throttle:300,1'])->group(function () {
    Route::get('/projects/{project}/structure', [MartApiController::class, 'getProjectStructure']);
    Route::post('/cases/{case}/submit', [MartApiController::class, 'submitEntry']);
    Route::post('/device-infos', [MartApiController::class, 'storeDeviceInfo']);
    Route::post('/stats', [MartApiController::class, 'submitStats']);

    // File upload endpoints for MART questionnaire answers
    Route::post('/cases/{case}/files', [MartFileController::class, 'store']);
    Route::get('/files/{martFileId}', [MartFileController::class, 'show']);
    Route::delete('/files/{martFileId}', [MartFileController::class, 'destroy']);
});
