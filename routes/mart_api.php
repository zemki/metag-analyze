<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MartApiController;

Route::middleware(['auth:api'])->group(function () {
    Route::get('/projects/{project}/structure', [MartApiController::class, 'getProjectStructure']);
    Route::post('/cases/{case}/submit', [MartApiController::class, 'submitEntry']);
});