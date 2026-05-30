<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use Illuminate\Support\Facades\Log;

class CategoriaController extends Controller
{
    /**
     * Lista todas las categorías disponibles.
     * Optimizado para el frontend de TestMind.
     */
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
                'error' => 'No se pudieron recuperar las categorías',
                'codigo' => 'ERR_CAT_01',
            ], 500);
        }
    }

    /**
     * Muestra una categoría específica.
     */
    public function show($id)
    {
        $categoria = Categoria::with('lenguajes')->find($id);

        if (! $categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        return $categoria;
    }
}
