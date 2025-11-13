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

// Email existence check - heavily rate limited to prevent enumeration attacks
// ⚠️ IMPORTANT: This endpoint is used by MART mobile apps to check if user exists
// before allowing self-registration
// 5 requests per minute per IP
Route::post('check-email', 'ApiController@checkEmailExists')->middleware('throttle:5,1');

// Send password setup email - very heavily rate limited to prevent abuse
// ⚠️ IMPORTANT: This endpoint is for MART projects only
// For MART mobile app users to self-register and set their password
// Non-MART projects should use the standard researcher-invites-user flow
// 3 requests per 10 minutes per IP
// Requires email to be checked first via /api/check-email
Route::post('send-password-setup', 'ApiController@sendPasswordSetup')->middleware('throttle:3,10');

// MART Authentication API Routes (3-Screen Flow)
// These endpoints implement a multi-step authentication flow for MART mobile apps:
// Screen 1: Email check → Screen 2: Password check → Screen 3: Project access check
// Each screen validates the previous screen was completed (using cache)
Route::prefix('mart')->group(function () {
    // Screen 1: Check if email exists
    Route::post('check-email', 'MartAuthController@checkEmail')
        ->middleware('throttle:5,1');

    // Screen 1: Send password setup email (for new users who click "Register")
    Route::post('send-password-setup', 'MartAuthController@sendPasswordSetup')
        ->middleware('throttle:3,10');

    // Screen 2: Authenticate with password and get tokens
    // Requires email to be checked in Screen 1 (within 1 minute)
    Route::post('check-password', 'MartAuthController@checkPassword')
        ->middleware('throttle:10,1');

    // Screen 3: Check project access and auto-create case
    // Requires password to be checked in Screen 2 (within 5 minutes)
    Route::post('check-access', 'MartAuthController@checkAccess')
        ->middleware('throttle:10,1');

    // Refresh access token using refresh token
    Route::post('refresh', 'MartAuthController@refreshToken')
        ->middleware('throttle:10,1');
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
