<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Intento;
use Illuminate\Support\Facades\Log;

class IntentoController extends Controller
{
    public function index()
    {
        try {
            $userId = auth()->id();

            $intentos = Intento::where('user_id', $userId)
                ->with([
                    'test:id,titulo,documento_id',
                    'test.documento:id,categoria_codigo',
                    'test.documento.categoria:id,codigo',
                    'test.documento.categoria.lenguajeActual',
                ])
                ->latest()
                ->get();

            $totalRealizados = $intentos->count();
            $mediaResultados = $totalRealizados > 0 ? round($intentos->avg('nota'), 2) : 0;

            $categoriaMasRealizada = 'Ninguna';

            if ($totalRealizados > 0) {
                $conteoCategorias = [];

                foreach ($intentos as $intento) {
                    $categoria = $intento->test?->documento?->categoria;
                    if ($categoria) {
                        $nombreTraducido = $categoria->descripcion;

                        if (! isset($conteoCategorias[$nombreTraducido])) {
                            $conteoCategorias[$nombreTraducido] = 0;
                        }
                        $conteoCategorias[$nombreTraducido]++;
                    }
                }

                if (! empty($conteoCategorias)) {
                    arsort($conteoCategorias);
                    $categoriaMasRealizada = key($conteoCategorias);
                }
            }

            $intentosMapeados = $intentos->map(function ($intento) {
                if ($intento->test?->documento?->categoria) {
                    $intento->test->documento->categoria->nombre_traducido = $intento->test->documento->categoria->descripcion;
                }

                return $intento;
            });

            return response()->json([
                'data' => [
                    'intentos' => $intentosMapeados,
                    'metrics' => [
                        'mediaResultados' => $mediaResultados,
                        'totalRealizados' => $totalRealizados,
                        'categoriaMasRealizada' => $categoriaMasRealizada,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error en IntentoController - index: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.IntentoController_index.500',
                'message' => 'Error interno al calcular las métricas y recuperar el historial de intentos.',
            ], 500);
        }
    }

    public function show(Intento $intento)
    {
        try {
            if ($intento->user_id !== auth()->id() && ! auth()->user()->tokenCan('admin')) {
                return response()->json([
                    'error_key' => 'error.IntentoController_show.403',
                    'message' => 'No tienes permiso para acceder a los detalles de este intento.',
                ], 403);
            }

            $intentoData = $intento->load(['test.documento.categoria.lenguajeActual']);

            return response()->json([
                'data' => [
                    'id' => $intentoData->id,
                    'nota' => $intentoData->nota,
                    'aciertos' => $intentoData->aciertos,
                    'total' => $intentoData->total,
                    'feedback' => $intentoData->feedback,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error en IntentoController - show: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.IntentoController_show.500',
                'message' => 'Error interno al recuperar la corrección del intento.',
            ], 500);
        }
    }
}
