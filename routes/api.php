<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentoController;
use App\Http\Controllers\Api\TestController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'refresh.token'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('abilities:user')->group(function () {
        
        Route::get('/user-profile', function (Request $request) {
            return $request->user();
        });

        Route::apiResource('documentos', DocumentoController::class);

        Route::apiResource('tests', TestController::class);
        
    });

    Route::middleware('abilities:admin')->group(function () {
        
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Bienvenido al panel de gestión, Admin.']);
        });

    });

});
