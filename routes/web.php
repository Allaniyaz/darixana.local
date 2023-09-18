<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/cars/import', 'App\Http\Controllers\CarController@import')->name('cars.import');
Route::post('/admin/cars/import', 'App\Http\Controllers\CarController@save_import')->name('cars.import-save');
Route::get('/admin/cars/import/errors', 'App\Http\Controllers\CarController@downloadImportErrors')->name('cars.import-errors');

Route::group(['middleware' => ['auth']], function () {
    Route::get('contract/download_blank/{contract}', 'App\Http\Controllers\ContractController@download_blank')->name('contract.download_blank');
    Route::get('contract/{contract}', 'App\Http\Controllers\ContractController@show')->name('contract.show');
});

