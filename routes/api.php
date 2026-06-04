<?php

use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\DocumentoController;
use App\Http\Controllers\Api\IntentoController;
use App\Http\Controllers\Api\InterfaceTranslationController;
use App\Http\Controllers\Api\TablaApoyoController;
use App\Http\Controllers\Api\TestController;
use App\Models\Lenguaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/i18n/{locale}', [InterfaceTranslationController::class, 'getJson']);
Route::get('/idiomas-disponibles', function() {
    try {
        return response()->json(
            Lenguaje::select(['codigo', 'descripcion'])->get()->values()
        );
    } catch (\Exception $e) {
        \Log::error('Fallo crítico en el inicializador dinámico de idiomas: ' . $e->getMessage());
        
        return response()->json([
            ['codigo' => 'es', 'descripcion' => 'Castellano']
        ]);
    }
});

Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);

Route::apiResource('/categoria', CategoriaController::class);
Route::get('/documentos', [DocumentoController::class, 'indexPublic']);
Route::get('/documentos/{documento}', [DocumentoController::class, 'show']);
Route::get('/documentos/{documento}/descargar', [DocumentoController::class, 'descargar']);

Route::get('/tests', [TestController::class, 'indexPublic']);
Route::get('/test/{test}/realizar', [TestController::class, 'realizar']);

Route::middleware(['auth:sanctum', 'refresh.token'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'data' => $user,
            'role' => $user->currentAccessToken()->abilities[0] ?? 'unknown',
            'type' => (new \ReflectionClass($user))->getShortName() == 'Admin' ? 'admin' : 'user',
        ]);
    });

    Route::prefix('user')->middleware('abilities:user')->group(function () {
        Route::apiResource('/documento', DocumentoController::class);
        Route::apiResource('/test', TestController::class);
        Route::post('/test/{test}/corregir', [TestController::class, 'corregir']);
        Route::get('/intento', [IntentoController::class, 'index']);
        Route::get('/intento/{intento}', [IntentoController::class, 'show']);
    });

    Route::prefix('admin')->middleware('abilities:admin')->group(function () {

        Route::post('/register', [AuthController::class, 'registerAdmin']);

        Route::prefix('metrics')->group(function () {
            Route::get('/users', [AdminDashboardController::class, 'userSegmentation']);
            Route::get('/tests-creados', [AdminDashboardController::class, 'testsCreadosTimeline']);
            Route::get('/categorias', [AdminDashboardController::class, 'testsByCategory']);
        });

        Route::prefix('lenguaje')->group(function () {
            Route::get('/', [InterfaceTranslationController::class, 'index']);
            Route::put('/update', [InterfaceTranslationController::class, 'updateKey']);
            Route::delete('/destroy', [InterfaceTranslationController::class, 'destroyKey']);
        });

        Route::prefix('tablaApoyo')->group(function () {
            Route::get('/', [TablaApoyoController::class, 'indexTablas']);
            Route::get('/{id}', [TablaApoyoController::class, 'readRows']);
            Route::post('/{id}/row', [TablaApoyoController::class, 'createRow']);
            Route::put('/{id}/row/{rowId}', [TablaApoyoController::class, 'updateRow']);
            Route::delete('/{id}/row/{rowId}', [TablaApoyoController::class, 'deleteRow']);
            Route::get('/{id}/row/{rowId}/lenguajes', [TablaApoyoController::class, 'getRowLanguages']);
            Route::put('/{id}/row/{rowId}/lenguajes', [TablaApoyoController::class, 'updateRowLanguages']);
        });
    });

});
