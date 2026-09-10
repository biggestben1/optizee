<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServerConfigController;
use App\Http\Controllers\Api\Staff\AuthController as StaffAuthController;
use App\Http\Controllers\Api\Staff\PosController as StaffPosController;
use App\Http\Controllers\Api\Staff\KitchenController as StaffKitchenController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Server configuration endpoints for mobile apps
Route::get('/server/config', [ServerConfigController::class, 'getConfig'])->name('api.server.config');
Route::get('/server/ip', [ServerConfigController::class, 'getIp'])->name('api.server.ip');
Route::post('/server/config', [ServerConfigController::class, 'updateConfig'])->name('api.server.config.update');

/*
|--------------------------------------------------------------------------
| Staff Mobile App (Cashier + Kitchen)
|--------------------------------------------------------------------------
*/
Route::prefix('staff')->group(function () {
    Route::post('/login', [StaffAuthController::class, 'login']);
    Route::post('/login/code', [StaffAuthController::class, 'loginWithCode']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [StaffAuthController::class, 'me']);
        Route::post('/logout', [StaffAuthController::class, 'logout']);

        Route::prefix('pos')->group(function () {
            Route::get('/catalog', [StaffPosController::class, 'catalog']);
            Route::get('/tables', [StaffPosController::class, 'tables']);
            Route::get('/tables/{table}/guests', [StaffPosController::class, 'tableGuests']);
            Route::post('/tables/{table}/guests', [StaffPosController::class, 'addGuest']);
            Route::get('/customers', [StaffPosController::class, 'customers']);
            Route::get('/ready-orders', [StaffPosController::class, 'readyOrders']);
            Route::post('/checkout', [StaffPosController::class, 'checkout']);
        });

        Route::prefix('kitchen')->group(function () {
            Route::get('/live', [StaffKitchenController::class, 'live']);
            Route::post('/{sale}/preparing', [StaffKitchenController::class, 'preparing']);
            Route::post('/{sale}/ready', [StaffKitchenController::class, 'ready']);
            Route::post('/{sale}/served', [StaffKitchenController::class, 'served']);
        });
    });
});
