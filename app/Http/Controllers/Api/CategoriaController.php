<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use Illuminate\Support\Facades\Log;

class CategoriaController extends Controller
{
    public function index()
    {
        try {
            $categorias = Categoria::with('lenguajeActual')
                ->where('valorUsado', true)
                ->get();

            return CategoriaResource::collection($categorias);

        } catch (\Exception $e) {
            Log::error('Error en TestMind CategoriaController@index: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.CategoriaController_index.500',
                'message' => 'No se pudieron recuperar las categorías.'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $categoria = Categoria::with('lenguajes')->find($id);

            if (! $categoria) {
                return response()->json([
                    'error_key' => 'error.CategoriaController_show.404',
                    'message' => 'Categoría no encontrada.'
                ], 404);
            }

            return $categoria;

        } catch (\Exception $e) {
            Log::error('Error en TestMind CategoriaController@show: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.CategoriaController_show.500',
                'message' => 'Error interno al recuperar los detalles de la categoría.'
            ], 500);
        }
    }
}