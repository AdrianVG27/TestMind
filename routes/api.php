<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentoController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\IntentoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum', 'refresh.token'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'data' => $user,
            'role' => $user->currentAccessToken()->abilities[0] ?? 'unknown',
            'type' => (new \ReflectionClass($user))->getShortName(),
        ]);
    });

    Route::middleware('abilities:user')->group(function () {
        Route::apiResource('documento', DocumentoController::class);
        Route::apiResource('test', TestController::class);
        Route::get('/test/{test}/realizar', [TestController::class, 'realizar']);
        Route::post('/test/{test}/corregir', [TestController::class, 'corregir']);
        Route::get('/intento', [IntentoController::class, 'index']);
        Route::get('/intento/{intento}', [IntentoController::class, 'show']);
    });

    Route::prefix('admin')->middleware('abilities:admin')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Panel de gestión TestMind activo.']);
        });
    });

});
