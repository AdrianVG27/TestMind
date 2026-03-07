<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
/*
Route::middleware('auth:sanctum')->group(function () {

    //* LISTAR usuarios
    Route::get('/users', [UserController::class, 'index']);

    //* CREAR un nuevo usuario
    Route::post('/users', [UserController::class, 'store']);

    //* MOSTRAR un usuario específico
    Route::get('/users/{id}', [UserController::class, 'show']);

    //* ACTUALIZAR un usuario
    Route::put('/users/{id}', [UserController::class, 'update']); // reemplaza todos los campos
    Route::patch('/users/{id}', [UserController::class, 'update']); // para actualizar solo algunos campos

    //* ELIMINAR un usuario
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
*/