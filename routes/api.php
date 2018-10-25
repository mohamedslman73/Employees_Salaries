<?php

use Illuminate\Http\Request;

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


Route::post('/login', 'AuthController@login');

Route::get('/listStaff', 'PayrollApiController@listStaff')->name('system.payroll.list-staff');
Route::group(['prefix' => 'payroll', 'middleware' => 'auth:api'], function () {
    Route::post('/create', 'PayrollApiController@create')->name('system.payroll.create');
    Route::get('/logout', 'SystemApiController@logout');
});