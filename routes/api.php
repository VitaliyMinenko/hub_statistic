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

Route::group(['middleware' => 'api'], function(){
    Route::post('get-information-by-repositories/', [
        'uses' => ApiController::class . '@compareRepositories',
        'as' => 'get-information-by-repositories',
    ]);
    Route::any('{path}', function() {
        return response()->json([
            'message' => 'Route not found'
        ], 404);
    })->where('path', '.*');
});

