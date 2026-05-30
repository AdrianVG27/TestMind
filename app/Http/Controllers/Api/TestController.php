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
                ->where('estado', 'completado')
                ->with(['documento.categoria.lenguajes'])
                ->when($request->titulo, function ($query, $titulo) {
                    $query->where('titulo', 'like', '%'.$titulo.'%');
                })
                ->when($request->categoria_id, function ($query, $categoriaId) {
                    $query->whereHas('documento', function ($q) use ($categoriaId) {
                        $q->where('categoria_id', $categoriaId);
                    });
                })
                ->latest()
                ->paginate(20);

            return TestResource::collection($tests);

        } catch (\Exception $e) {
            Log::error('Error en TestMind - indexPublic: '.$e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar los tests públicos',
                'codigo' => 'ERR_TEST_02',
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
                ->when($request->categoria_id, function ($query, $categoriaId) {
                    $query->whereHas('documento', function ($q) use ($categoriaId) {
                        $q->where('categoria_id', $categoriaId);
                    });
                })
                ->latest()
                ->paginate(20);

            return TestEditResource::collection($tests);

        } catch (\Exception $e) {
            Log::error('Error en TestMind - index (privado): '.$e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar tus cuestionarios privados',
                'codigo' => 'ERR_TEST_01',
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

        $documento = Documento::where('id', $request->documento_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $test = Test::create([
            'documento_id' => $documento->id,
            'titulo' => $request->titulo,
            'configuracion' => $request->only(['nivel', 'total', 'prop_unica', 'prop_multi', 'prop_escribir', 'min_opciones', 'max_opciones', 'input_user']),
            'estado' => 'pendiente',
        ]);

        GenerarTestJob::dispatch($test);

        return response()->json($test, 202);
    }

    public function show(Test $test)
    {
        if ($test->documento->user_id !== auth()->id()) {
            abort(403, 'No tienes permisos para ver la configuración de este cuestionario.');
        }

        try {
            $test->load('documento.categoria');

            return new TestEditResource($test);

        } catch (\Exception $e) {
            Log::error('Error en TestMind al cargar el detalle del Test #'.$test->id.': '.$e->getMessage());

            return response()->json([
                'error' => 'No se pudo recuperar la configuración del cuestionario académico.',
                'codigo' => 'ERR_TEST_SHOW_01',
            ], 500);
        }
    }

    public function update(Request $request, Test $test)
    {
        if ($test->documento->user_id !== auth()->id()) {
            abort(403);
        }

        $test->update(['configuracion' => $request->input('configuracion'), 'estado' => 'pendiente']);
        GenerarTestJob::dispatch($test);

        return response()->json($test);
    }

    public function destroy(Test $test)
    {
        if ($test->documento->user_id !== auth()->id()) {
            abort(403);
        }
        $test->delete();

        return response()->json(null, 204);
    }

    public function realizar(Test $test)
    {
        $preguntasOcultas = collect($test->preguntas)->map(function ($pregunta) {
            unset($pregunta['respuesta_correcta']);

            return $pregunta;
        });

        return response()->json([
            'id' => $test->id,
            'titulo' => $test->titulo,
            'configuracion' => $test->configuracion,
            'preguntas' => $preguntasOcultas,
            'estado' => $test->estado,
        ]);
    }

    public function corregir(Request $request, Test $test)
    {
        $request->validate([
            'respuestas' => 'required|array',
            'duracion' => 'nullable|integer',
        ]);

        $respuestasUsuario = $request->input('respuestas');
        $preguntasOriginales = $test->preguntas;

        $aciertos = 0;
        $totalPreguntas = count($preguntasOriginales);
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
    }
}
