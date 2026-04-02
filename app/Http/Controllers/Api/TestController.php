<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerarTestJob;
use App\Models\Documento;
use App\Models\Intento;
use App\Models\Test;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->test()->with('documento')->latest()->get());
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
            abort(403);
        }

        return response()->json($test->load('documento'));
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

            $esCorrecta = ($enviada === $correcta);

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
            'feedback' => $detalles,
        ], 201);
    }
}
