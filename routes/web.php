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

use App\Http\Controllers\EntryController;

Auth::routes(['verify' => true]);

// Test route for Vue 3
Route::get('/test-vue3', function () {
    return view('test-vue3');
});

Route::group(['middleware' => ['throttle:3,10']], static function () {
    Route::get('/password/set', 'Auth\VerificationController@showresetpassword');
    Route::get('/password/reset-form', 'Auth\ResetPasswordController@reset')->name('password.reset.form');
    Route::get('/setpassword', 'Auth\VerificationController@showresetpassword');
    Route::post('/password/new', 'Auth\VerificationController@newpassword');
    Route::get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');
    Route::post('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');
});

Route::group(['middleware' => ['auth', 'authorised', 'verified', 'LoggedUser']], function () {
    Route::get('/verifyNewEmail', 'Auth\EmailChangeController@verify')->name('verifyNewEmail');
    Route::post('/changeemail', 'Auth\EmailChangeController@change')->name('change');

    /**
     * Backend Entry routes
     */
    Route::post('/cases/{case}/entries', [EntryController::class, 'store']);
    Route::patch('/cases/{case}/entries/{entry}', [EntryController::class, 'update']);
    Route::delete('/cases/{case}/entries/{entry}', [EntryController::class, 'destroy']);

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
    Route::delete('/projects/{project}', 'ProjectController@destroy')->name('projects.destroy');

    Route::get('/projects/{project}/export', 'ProjectController@export');
    Route::post('/projects/invite', 'ProjectController@inviteUser');
    Route::post('/projects/invite/{user}', 'ProjectController@removeFromProject');
    Route::get('/projects/{project}/duplicate', 'ProjectController@duplicate');

    /**
     * Case Routes
     * Case is dependant of project, so we concatenate with it
     */
    Route::get('/projects/{project}/notifications', 'ProjectNotificationController@show');
    Route::get('/projects/{project}/cases/new', 'ProjectCasesController@create');
    Route::post('/projects/{project}/cases', 'ProjectCasesController@store');
    Route::get('/projects/{project}/distinctcases/{case}', 'ProjectCasesController@distinctshow');
    Route::get('/projects/{project}/groupedcases/{case}', 'ProjectCasesController@groupedshow');
    Route::get('/cases/{case}/export', 'ProjectCasesController@export');
    Route::patch('/projects/{project}/cases/{case}', 'ProjectCasesController@update');
    Route::delete('/cases/{case}', 'ProjectCasesController@destroy')->name('cases.destroy');
    Route::get('/cases/{case}/files', 'FileCasesController@index');
    Route::delete('/cases/{case}/files/{file}', 'FileCasesController@destroy');

    /**
     * User Routes
     */
    Route::post('/users/password/reset', 'Auth\ForgotPasswordController@SendsPasswordResetEmailFromCasesList');
    Route::post('/users/subscribe', 'UserController@addToNewsletter');
    Route::post('/users/notify', 'UserController@notifyDevice');
    Route::post('/users/plannotification', 'UserController@planNotification');
    Route::post('/users/deletenotification', 'UserController@deletePlannedNotification');
    Route::post('/users/cleanuplastnotification', 'UserController@cleanupNotifications');
});
