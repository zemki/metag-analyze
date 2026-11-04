<?php

use App\Http\Controllers\MartApiController;
use App\Http\Controllers\MartAuthController;
use App\Http\Controllers\MartFileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API Routes
|--------------------------------------------------------------------------
|
| Everything the MART mobile app talks to, under one prefix: `/api/mobile`.
| The older `/api/mart/*` (auth) and `/mart-api/*` (data) routes stay as
| aliases until no app version in use calls them.
|
| The `auth/` segment marks the unauthenticated endpoints. Everything outside
| it requires a Bearer token, so the URL says which rule applies.
|
*/

Route::middleware('force.json')->name('mobile.')->group(function () {

    /*
     * The 3-screen auth flow. Each step checks that the previous one completed
     * by looking for its cache entry, so they are unauthenticated but each is
     * throttled per IP. The key prefixes keep password setup's ten-minute window
     * apart from the others: on a shared counter, the check-email that always
     * comes first would make it expire after one minute.
     */
    Route::prefix('auth')->name('auth.')->group(function () {
        // Screen 1: does this email exist?
        Route::post('check-email', [MartAuthController::class, 'checkEmail'])
            ->middleware('throttle:30,1,mobile-auth')
            ->name('check-email');

        // Screen 1b: new user clicked "Register". Stricter limit because it
        // triggers outbound email.
        Route::post('send-password-setup', [MartAuthController::class, 'sendPasswordSetup'])
            ->middleware('throttle:30,10,mobile-password-setup')
            ->name('send-password-setup');

        // Screen 2: authenticate and get a token pair. Requires Screen 1
        // within the last minute.
        Route::post('check-password', [MartAuthController::class, 'checkPassword'])
            ->middleware('throttle:30,1,mobile-auth')
            ->name('check-password');

        // Screen 3: project access, auto-creating the case. Requires Screen 2
        // within the last five minutes.
        Route::post('check-access', [MartAuthController::class, 'checkAccess'])
            ->middleware('throttle:30,1,mobile-auth')
            ->name('check-access');

        // Exchange a refresh token for a fresh pair.
        Route::post('refresh', [MartAuthController::class, 'refreshToken'])
            ->middleware('throttle:30,1,mobile-auth')
            ->name('refresh');
    });

    /*
     * Data endpoints. Higher throttle (300/min/IP) than Laravel's default of
     * 60/min because participants poll /structure frequently, submit entries in
     * bursts, and upload device-info / stats payloads alongside questionnaires.
     * This matches the volume seen during researcher testing and real mobile use.
     */
    Route::middleware(['auth:api', 'throttle:300,1'])->group(function () {
        Route::get('projects/{project}/structure', [MartApiController::class, 'getProjectStructure'])
            ->name('structure');
        Route::post('cases/{case}/submit', [MartApiController::class, 'submitEntry'])
            ->name('submit');
        Route::post('device-infos', [MartApiController::class, 'storeDeviceInfo'])
            ->name('device-infos');

        Route::post('device-token', [MartApiController::class, 'storeDeviceToken'])
            ->name('device-token');
        Route::post('stats', [MartApiController::class, 'submitStats'])
            ->name('stats');

        // Files attached to questionnaire answers.
        Route::post('cases/{case}/files', [MartFileController::class, 'store'])
            ->name('files.store');
        Route::get('files/{martFileId}', [MartFileController::class, 'show'])
            ->name('files.show');
        Route::delete('files/{martFileId}', [MartFileController::class, 'destroy'])
            ->name('files.destroy');
    });
});
