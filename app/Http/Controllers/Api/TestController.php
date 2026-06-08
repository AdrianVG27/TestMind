<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TestEditResource;
use App\Http\Resources\TestResource;
use App\Jobs\GenerarTestJob;
use App\Models\Documento;
use App\Models\Intento;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function indexPublic(Request $request)
    {
        try {
            $tests = Test::query()
                ->where('estado_codigo', 'C')
                ->with(['documento.categoria.lenguajes'])
                ->when($request->titulo, function ($query, $titulo) {
                    $query->where('titulo', 'like', '%'.$titulo.'%');
                })
                ->when($request->categoria_codigo, function ($query, $categoriaCodigo) {
                    $query->whereHas('documento', function ($q) use ($categoriaCodigo) {
                        $q->where('categoria_codigo', $categoriaCodigo);
                    });
                })
                ->latest()
                ->paginate(20);

            return TestResource::collection($tests);

        } catch (\Exception $e) {
            Log::error('Error en TestController - indexPublic: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.TestController_indexPublic.500',
                'message' => 'Error al recuperar los tests públicos.'
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $tests = $request->user()->test()
                ->with('documento')
                ->when($request->titulo, function ($query, $titulo) {
                    $query->where('titulo', 'like', '%'.$titulo.'%');
                })
                ->when($request->categoria_codigo, function ($query, $categoriaCodigo) {
                    $query->whereHas('documento', function ($q) use ($categoriaCodigo) {
                        $q->where('categoria_codigo', $categoriaCodigo);
                    });
                })
                ->latest()
                ->paginate(20);

            return TestEditResource::collection($tests);
        } catch (\Exception $e) {
            Log::error('Error en TestController - index (privado): '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.TestController_index.500',
                'message' => 'Error al recuperar tus cuestionarios privados.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'documento_id' => 'required|exists:documento,id',
            'titulo' => 'required|string',
            'nivel' => 'required|string',
            'total' => 'required|integer|min:1',
            'prop_unica' => 'required|integer',
            'prop_multi' => 'required|integer',
            'prop_escribir' => 'required|integer',
            'min_opciones' => 'required|integer',
            'max_opciones' => 'required|integer',
            'input_user' => 'nullable|string|max:500',
        ]);

        try {
            $documento = Documento::where('id', $request->documento_id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$documento) {
                return response()->json([
                    'error_key' => 'error.TestController_store.404_document',
                    'message' => 'El apunte referenciado no existe o no eres su propietario.'
                ], 404);
            }

            $test = Test::create([
                'documento_id' => $documento->id,
                'titulo' => $request->titulo,
                'configuracion' => $request->only(['nivel', 'total', 'prop_unica', 'prop_multi', 'prop_escribir', 'min_opciones', 'max_opciones', 'input_user']),
                'estado_codigo' => 'P',
            ]);

            GenerarTestJob::dispatch($test);

            return response()->json($test, 202);

        } catch (\Exception $e) {
            Log::error('Error en TestController - store: '.$e->getMessage());
            return response()->json([
                'error_key' => 'error.TestController_store.500',
                'message' => 'Fallo interno al encolar la petición de generación con IA.'
            ], 500);
        }
    }

    public function show(Test $test)
    {
        try {
            if ($test->documento->user_id !== auth()->id()) {
                return response()->json([
                    'error_key' => 'error.TestController_show.403',
                    'message' => 'No tienes permisos para ver la configuración de este cuestionario.'
                ], 403);
            }

            $test->load('documento.categoria');

            return new TestEditResource($test);

        } catch (\Exception $e) {
            Log::error('Error en TestController - show #'.$test->id.': '.$e->getMessage());
            return response()->json([
                'error_key' => 'error.TestController_show.500',
                'message' => 'No se pudo recuperar la configuración del cuestionario académico.'
            ], 500);
        }
    }

    public function update(Request $request, Test $test)
    {
        if ($test->documento->user_id !== auth()->id()) {
            return response()->json([
                'error_key' => 'error.TestController_update.403',
                'message' => 'No tienes permisos para actualizar este cuestionario.'
            ], 403);
        }

        try {
            $test->update(['configuracion' => $request->input('configuracion'), 'estado_codigo' => 'P']);
            
            GenerarTestJob::dispatch($test);

            return response()->json($test);

        } catch (\Exception $e) {
            Log::error('Error en TestController - update: '.$e->getMessage());
            return response()->json([
                'error_key' => 'error.TestController_update.500',
                'message' => 'Error interno al actualizar la configuración y relanzar la IA.'
            ], 500);
        }
    }

    public function destroy(Test $test)
    {
        if ($test->documento->user_id !== auth()->id()) {
            return response()->json([
                'error_key' => 'error.TestController_destroy.403',
                'message' => 'No tienes permisos para eliminar este cuestionario.'
            ], 403);
        }

        try {
            $test->delete();

            return response()->json(null, 204);

        } catch (\Exception $e) {
            Log::error('Error en TestController - destroy: '.$e->getMessage());
            return response()->json([
                'error_key' => 'error.TestController_destroy.500',
                'message' => 'Error de consistencia al purgar el test.'
            ], 500);
        }
    }

    public function realizar(Test $test)
    {
        try {
            $preguntasOcultas = collect($test->preguntas)->map(function ($pregunta) {
                unset($pregunta['respuesta_correcta']);
                return $pregunta;
            });

            return response()->json([
                'id' => $test->id,
                'titulo' => $test->titulo,
                'configuracion' => $test->configuracion,
                'preguntas' => $preguntasOcultas,
                'estado_codigo' => $test->estado_codigo,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en TestController - realizar: '.$e->getMessage());
            return response()->json([
                'error_key' => 'error.TestController_realizar.500',
                'message' => 'Fallo al inicializar la instancia para realizar el examen.'
            ], 500);
        }
    }

    public function corregir(Request $request, Test $test)
    {
        $request->validate([
            'respuestas' => 'required|array',
            'duracion' => 'nullable|integer',
        ]);

        try {
            $respuestasUsuario = $request->input('respuestas');
            $preguntasOriginales = $test->preguntas;

            $aciertos = 0;
            $totalPreguntas = count($preguntasOriginales);
            
            if ($totalPreguntas === 0) {
                return response()->json([
                    'error_key' => 'error.TestController_corregir.422_empty',
                    'message' => 'Este cuestionario no cuenta con preguntas cargadas para corregir.'
                ], 422);
            }

            $detalles = [];

            foreach ($preguntasOriginales as $index => $pregunta) {
                $correcta = $pregunta['respuesta_correcta'];
                $enviada = $respuestasUsuario[$index] ?? null;

                if (is_array($enviada) || is_array($correcta)) {
                    $copiaEnviada = (array) $enviada;
                    $copiaCorrecta = (array) $correcta;

                    sort($copiaEnviada);
                    sort($copiaCorrecta);

                    $esCorrecta = ($copiaEnviada === $copiaCorrecta);
                } else {
                    $cleanEnviada = trim(strtolower((string) ($enviada ?? '')));
                    $cleanCorrecta = trim(strtolower((string) ($correcta ?? '')));

                    $esCorrecta = ($cleanEnviada === $cleanCorrecta);
                }

                if ($esCorrecta) {
                    $aciertos++;
                }

                $detalles[] = [
                    'enunciado' => $pregunta['enunciado'],
                    'tu_respuesta' => $enviada,
                    'correcta' => $correcta,
                    'acierto' => $esCorrecta,
                ];
            }

            $nota = ($aciertos / $totalPreguntas) * 10;

            $intento = Intento::create([
                'user_id' => auth()->id(),
                'test_id' => $test->id,
                'respuestas_usuario' => $respuestasUsuario,
                'feedback' => $detalles,
                'aciertos' => $aciertos,
                'total_preguntas' => $totalPreguntas,
                'nota' => round($nota, 2),
                'duracion_segundos' => $request->input('duracion'),
            ]);

            return response()->json([
                'intento_id' => $intento->id,
                'nota' => $intento->nota,
                'aciertos' => $intento->aciertos,
                'total' => $intento->total_preguntas,
                'feedback' => $intento->feedback,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error en TestController - corregir: '.$e->getMessage());
            return response()->json([
                'error_key' => 'error.TestController_corregir.500',
                'message' => 'Error crítico al procesar la calificación y el guardado de tu intento.'
            ], 500);
        }
    }
}