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

// Route::get('/', function () {
// 	return view('welcome');
// });

Route::group(['middleware' => 'auth'], function(){
	Route::get('/','ProjectController@index');
	Route::get('/projects','ProjectController@index');
	Route::post('/projects','ProjectController@store');
	Route::get('/projects/new','ProjectController@create');
	Route::get('/projects/{project}','ProjectController@show');
	Route::post('/users','UserController@store');
	Route::get('/home', 'HomeController@index')->name('home');


});

