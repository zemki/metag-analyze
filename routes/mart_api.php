<?php

use App\Http\Controllers\MartApiController;
use App\Http\Controllers\MartFileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::get('/projects/{project}/structure', [MartApiController::class, 'getProjectStructure']);
    Route::post('/cases/{case}/submit', [MartApiController::class, 'submitEntry']);
    Route::post('/device-infos', [MartApiController::class, 'storeDeviceInfo']);
    Route::post('/stats', [MartApiController::class, 'submitStats']);

    // File upload endpoints for MART questionnaire answers
    Route::post('/cases/{case}/files', [MartFileController::class, 'store']);
    Route::get('/files/{martFile}', [MartFileController::class, 'show']);
    Route::delete('/files/{martFile}', [MartFileController::class, 'destroy']);
});
