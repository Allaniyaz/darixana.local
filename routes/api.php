<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;



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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [UserController::class, 'login'])->name('login');

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'v1.0', 'namespace' => 'API\v1_0',], function() {

});

Route::any('{path}', function() {
    return response()->json([
        'message' => 'API method not found'
    ], 404);
})->where('path', '.*');
