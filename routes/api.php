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



Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'payroll'], function () {
        Route::post('/create', 'PayrollApiController@create')->name('system.payroll.create');
        Route::get('/allPayrolls', 'PayrollApiController@allPayrolls')->name('system.payroll.all');
    });
    Route::group(['prefix' => 'bonus'], function () {
        Route::post('/create','BonusApiController@create')->name('system.bonus.create');
        Route::get('/allBonuses','BonusApiController@allBonuses')->name('system.bonus.all');
        Route::get('/oneBonus','BonusApiController@oneBonus')->name('system.bonus.show');
    });
    Route::get('/logout', 'SystemApiController@logout');
    Route::get('/listStaff', 'PayrollApiController@listStaff')->name('system.payroll.list-staff');
});