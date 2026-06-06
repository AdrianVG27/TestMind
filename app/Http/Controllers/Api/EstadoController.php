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
            $estados = Estado::with('lenguajeActual')
                ->where('valorUsado', true)
                ->get();

            return EstadoResource::collection($estados);

        } catch (\Exception $e) {
            Log::error('Error en TestMind EstadoController@index: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.EstadoController_index.500',
                'message' => 'No se pudieron recuperar los estados.',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $estado = Estado::with('lenguajes')->find($id);

            if (! $estado) {
                return response()->json([
                    'error_key' => 'error.EstadoController_show.404',
                    'message' => 'Estado no encontrado.',
                ], 404);
            }

            return $estado;

        } catch (\Exception $e) {
            Log::error('Error en TestMind EstadoController@show: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.EstadoController_show.500',
                'message' => 'Error interno al recuperar los detalles del estado.',
            ], 500);
        }
    }
}
