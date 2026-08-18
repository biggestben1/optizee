<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServerConfigController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Server configuration endpoints for mobile apps
Route::get('/server/config', [ServerConfigController::class, 'getConfig'])->name('api.server.config');
Route::get('/server/ip', [ServerConfigController::class, 'getIp'])->name('api.server.ip');
Route::post('/server/config', [ServerConfigController::class, 'updateConfig'])->name('api.server.config.update');
