<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EstadoResource;
use App\Models\Estado;
use Illuminate\Support\Facades\Log;

class EstadoController extends Controller
{
    public function index()
    {
        try {
            $categorias = Estado::with('lenguajeActual')
                ->where('valorUsado', true)
                ->get();

            return EstadoResource::collection($categorias);

        } catch (\Exception $e) {
            Log::error('Error en TestMind EstadoController@index: '.$e->getMessage());

            return response()->json([
                'error' => 'No se pudieron recuperar los estados',
                'codigo' => 'ERR_ESTADO_01',
            ], 500);
        }
    }

    /**
     * Muestra una categoría específica.
     */
    public function show($id)
    {
        $categoria = Estado::with('lenguajes')->find($id);

        if (! $categoria) {
            return response()->json(['message' => 'Estado no encontrada'], 404);
        }

        return $categoria;
    }
}
