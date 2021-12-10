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

Route::get('/password/set', 'Auth\VerificationController@showresetpassword');
Route::get('/setpassword', 'Auth\VerificationController@showresetpassword');
Route::post('/password/new', 'Auth\VerificationController@newpassword');

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'verified', 'haspowers', 'LoggedUser']], static function () {
    /**
     * Admin Routes
     */
    Route::get('/', 'AdminController@index');
    Route::get('/users', 'AdminController@indexUsers');
    Route::get('/cases', 'AdminController@indexCases');
    Route::get('/users/new', 'UserController@create')->name('newadminusers');
    Route::get('/deletedeviceid/{user}', 'AdminController@deletedeviceid')->name('deletedeviceid');
    Route::get('/resetapitoken/{user}', 'AdminController@resetapitoken')->name('resetapitoken');
    Route::get('/newsletter', 'AdminController@listForNewsletter');
});


Route::group(['middleware' => ['auth', 'authorised', 'verified', 'LoggedUser']], function () {

    /**
     * Backend Entry routes
     */
    Route::post('/cases/{case}/entries', 'EntryController@store');
    Route::patch('/cases/{case}/entries/{entry}', 'EntryController@update');
    Route::delete('/cases/{case}/entries/{entry}', 'EntryController@destroy');


    /**
     * Project Routes
     */
    Route::get('/', 'ProjectController@index');
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
    Route::get('/projects/{project}/notifications', 'ProjectNotificationController@show');
    Route::get('/projects/{project}/cases/new', 'ProjectCasesController@create');
    Route::post('/projects/{project}/cases', 'ProjectCasesController@store');
    //Route::get('/projects/{project}/cases/{case}', 'ProjectCasesController@show');
    Route::get('/projects/{project}/distinctcases/{case}', 'ProjectCasesController@distinctshow');
    Route::get('/projects/{project}/groupedcases/{case}', 'ProjectCasesController@groupedshow');
    Route::get('/cases/{case}/export', 'ProjectCasesController@export');
    Route::patch('/projects/{project}/cases/{case}', 'ProjectCasesController@update');
    Route::delete('/cases/{case}', 'ProjectCasesController@destroy');
    Route::get('/cases/{case}/files', 'FileCasesController@index');
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
    Route::post('/users/password/reset', 'Auth\ForgotPasswordController@SendsPasswordResetEmailFromCasesList');
    Route::post('/users/exist', 'UserController@userExists');
    Route::post('/users/subscribe', 'UserController@addToNewsletter');
    Route::post('/users/notify', 'UserController@notifyDevice');
    Route::post('/users/plannotification', 'UserController@planNotification');
    Route::post('/users/deletenotification', 'UserController@deletePlannedNotification');
    Route::post('/users/cleanuplastnotification', 'UserController@cleanupNotifications');
});
