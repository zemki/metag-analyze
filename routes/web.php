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

Auth::routes();


Route::group(['middleware' => ['auth','authorised']], function(){
	Route::get('/','ProjectController@index');

	/**
	 * Project Routes
	 */

	Route::get('/home','ProjectController@index');
	Route::get('/projects','ProjectController@index');
	Route::post('/projects','ProjectController@store');
	Route::get('/projects/new','ProjectController@create');
	Route::get('/projects/{project}','ProjectController@show');
	Route::patch('/projects/{project}','ProjectController@update');
	Route::delete('/projects/{project}','ProjectController@destroy');

	/**
	 * Case Routes
	 * Case is dependant of project, so we concatenate with it
	 */

	Route::get('/projects/{project}/cases/new','ProjectCasesController@create');
	Route::post('/projects/{project}/cases','ProjectCasesController@store');
	Route::get('/projects/{project}/cases/{case}','ProjectCasesController@show');
	Route::patch('/projects/{project}/cases/{case}','ProjectCasesController@update');
	Route::delete('/projects/{project}/cases/{case}','ProjectCasesController@destroy');

	/**
	 * Media Group Routes
	 */
	Route::get('/media_groups','Media_groupController@index');
	Route::post('/media_groups','Media_groupController@store');
	Route::get('/media_groups/new','Media_groupController@create');
	Route::get('/media_groups/{project}','Media_groupController@show');

	/**
	 * Media Routes
	 */
	Route::get('/media','MediaController@index');
	Route::post('/media','MediaController@store');
	Route::get('/media/new','MediaController@create');
	Route::get('/media/{project}','MediaController@show');

	Route::post('/users','UserController@store');

});

