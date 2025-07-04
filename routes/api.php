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

Route::post('login', 'ApiController@login')->middleware('throttle:10,1');

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
