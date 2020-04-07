<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Auth::routes(['verify' => true]);
Auth::routes();

Route::get('/setpassword', 'UserController@showresetpassword');
Route::post('/newpassword', 'UserController@newpassword');

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'authorised', 'verified', 'haspowers']], static function () {
    /**
     * Admin Routes
     */
    Route::get('/', 'AdminController@index');
    Route::get('/users', 'AdminController@indexUsers');
    Route::get('/users/new', 'UserController@create')->name('newadminusers');
    Route::get('/deletedeviceid/{user}', 'AdminController@deletedeviceid')->name('deletedeviceid');
    Route::get('/resetapitoken/{user}', 'AdminController@resetapitoken')->name('resetapitoken');
});

Route::group(['middleware' => ['auth', 'authorised']], function () {
    /**
     * Group Routes
     */
    Route::get('/groups/new', 'GroupController@create')->name('new_group');
    Route::post('/groups/store', 'GroupController@store')->name('store_groups');
});
Route::group(['middleware' => ['auth', 'authorised', 'verified']], function () {
    Route::get('/', 'ProjectController@index');
    /**
     * Project Routes
     */
    Route::get('/home', 'ProjectController@index');
    Route::get('/projects', 'ProjectController@index');
    Route::post('/projects', 'ProjectController@store')->name('projects');
    Route::get('/projects/new', 'ProjectController@create');
    Route::get('/projects/{project}', 'ProjectController@show');
    Route::patch('/projects/{project}', 'ProjectController@update');
    Route::delete('/projects/{project}', 'ProjectController@destroy');
    Route::get('/projects/{project}/export', 'ProjectController@export');
    Route::post('/projects/invite', 'ProjectController@inviteUser');
    Route::post('/projects/invite/{user}', 'ProjectController@removeFromProject');
    /**
     * Case Routes
     * Case is dependant of project, so we concatenate with it
     */
    Route::get('/projects/{project}/cases/new', 'ProjectCasesController@create');
    Route::post('/projects/{project}/cases', 'ProjectCasesController@store');
    Route::get('/projects/{project}/cases/{case}', 'ProjectCasesController@show');
    Route::get('/cases/{case}/export', 'ProjectCasesController@export');
    Route::patch('/projects/{project}/cases/{case}', 'ProjectCasesController@update');
    Route::delete('/cases/{case}', 'ProjectCasesController@destroy');
    /**
     * Media Routes
     */
    Route::get('/media', 'MediaController@index');
    Route::post('/media', 'MediaController@store');
    Route::get('/media/new', 'MediaController@create');
    Route::get('/media/{project}', 'MediaController@show');
    /**
     * User Routes
     */
    Route::post('/users', 'UserController@store')->name('users');
    Route::post('/users/exist', 'UserController@userExists');
    Route::post('/cases/exist', 'CaseController@caseExists');
});

