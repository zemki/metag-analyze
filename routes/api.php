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

Route::post('login', 'ApiController@login');

// QR Login (no auth required, throttled)
Route::post('qr-login', 'QrLoginController@loginWithQr')->middleware('throttle:10,1');

// Debug endpoint for QR token (temporary - remove in production)
Route::post('qr-login/debug', 'QrLoginController@debugQrToken')->middleware('throttle:10,1');

Route::group(['prefix' => 'v1', 'middleware' => ['auth:api']], function () {
    Route::get('/project/{project}', 'ApiController@getProject');

    Route::get('/inputs/{project}', 'ApiController@getInputs');
    Route::get('/entry/{case}', 'EntryController@entriesByCase');

    Route::post('/cases/{case}/entries', 'EntryController@store');
    Route::patch('/cases/{case}/entries/{entry}', 'EntryController@update');
    Route::delete('/cases/{case}/entries/{entry}', 'EntryController@destroy');
});
