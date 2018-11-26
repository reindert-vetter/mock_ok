<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::get('/test', function (Request $request) {
    return json_encode([1 => 'test1']);
});
Route::any('/dev/{all}', [\App\Domains\Collect\Controllers\CollectorController::class, 'handle'])->where('all', '.*');
