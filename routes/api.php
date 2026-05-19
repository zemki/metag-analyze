<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'ApiController@login')->middleware('throttle:10,60');

// Settings API
Route::get('settings/api_v2_cutoff_date', function () {
    return response()->json(['value' => \App\Setting::get('api_v2_cutoff_date', '2025-12-21')]);
});

// QR Code Login - rate limited to prevent brute force attacks
// For API v2 non-MART projects only
// 30 requests per minute per IP (bumped from 10 to support testing flows)
Route::post('qr-login', 'ApiController@qrLogin')->middleware('throttle:30,1');

// Email existence check - rate limited to prevent enumeration attacks
// ⚠️ IMPORTANT: This endpoint is used by MART mobile apps to check if user exists
// before allowing self-registration
// 30 requests per minute per IP (bumped from 5)
Route::post('check-email', 'ApiController@checkEmailExists')->middleware('throttle:30,1');

// Send password setup email - rate limited to prevent abuse (emails cost money)
// ⚠️ IMPORTANT: This endpoint is for MART projects only
// For MART mobile app users to self-register and set their password
// Non-MART projects should use the standard researcher-invites-user flow
// 10 requests per 10 minutes per IP (bumped from 3 to support testing flows)
// Requires email to be checked first via /api/check-email
Route::post('send-password-setup', 'ApiController@sendPasswordSetup')->middleware('throttle:10,10');

// MART Authentication API Routes (3-Screen Flow)
// These endpoints implement a multi-step authentication flow for MART mobile apps:
// Screen 1: Email check → Screen 2: Password check → Screen 3: Project access check
// Each screen validates the previous screen was completed (using cache)
Route::prefix('mart')->middleware('force.json')->group(function () {
    // Screen 1: Check if email exists
    // 30/min per IP (bumped from 10)
    Route::post('check-email', 'MartAuthController@checkEmail')
        ->middleware('throttle:30,1');

    // Screen 1: Send password setup email (for new users who click "Register")
    // Stricter limit because it triggers outbound emails
    // 30/10min per IP (bumped from 10)
    Route::post('send-password-setup', 'MartAuthController@sendPasswordSetup')
        ->middleware('throttle:30,10');

    // Screen 2: Authenticate with password and get tokens
    // Requires email to be checked in Screen 1 (within 1 minute)
    // 30/min per IP (bumped from 10)
    Route::post('check-password', 'MartAuthController@checkPassword')
        ->middleware('throttle:30,1');

    // Screen 3: Check project access and auto-create case
    // Requires password to be checked in Screen 2 (within 5 minutes)
    // 30/min per IP (bumped from 10)
    Route::post('check-access', 'MartAuthController@checkAccess')
        ->middleware('throttle:30,1');

    // Refresh access token using refresh token
    // 30/min per IP (bumped from 10)
    Route::post('refresh', 'MartAuthController@refreshToken')
        ->middleware('throttle:30,1');
});

// V1 API Routes (Legacy - uses 'media' field)
Route::group(['prefix' => 'v1', 'middleware' => ['auth:api']], function () {
    Route::get('/project/{project}', 'Api\V1\ApiController@getProject');

    Route::get('/inputs/{project}', 'Api\V1\ApiController@getInputs');
    Route::get('/entry/{case}', 'Api\V1\EntryController@entriesByCase');

    Route::post('/cases/{case}/entries', 'Api\V1\EntryController@store');
    Route::patch('/cases/{case}/entries/{entry}', 'Api\V1\EntryController@update');
    Route::delete('/cases/{case}/entries/{entry}', 'Api\V1\EntryController@destroy');
});

// V2 API Routes (New - uses 'entity' field)
Route::group(['prefix' => 'v2', 'middleware' => ['auth:api']], function () {
    Route::get('/project/{project}', 'Api\V2\ApiController@getProject');

    Route::get('/inputs/{project}', 'Api\V2\ApiController@getInputs');
    Route::get('/entry/{case}', 'Api\V2\EntryController@entriesByCase');

    Route::post('/cases/{case}/entries', 'Api\V2\EntryController@store');
    Route::patch('/cases/{case}/entries/{entry}', 'Api\V2\EntryController@update');
    Route::delete('/cases/{case}/entries/{entry}', 'Api\V2\EntryController@destroy');

    Route::get('/files/{file}', 'Api\V2\FileController@show');

    // Pages API routes
    Route::get('/projects/{project}/pages', 'Api\V2\PageController@index');
    Route::post('/projects/{project}/pages', 'Api\V2\PageController@store');
    Route::get('/projects/{project}/pages/{page}', 'Api\V2\PageController@show');
    Route::patch('/projects/{project}/pages/{page}', 'Api\V2\PageController@update');
    Route::delete('/projects/{project}/pages/{page}', 'Api\V2\PageController@destroy');
    Route::patch('/projects/{project}/pages/order', 'Api\V2\PageController@updateOrder');
});
