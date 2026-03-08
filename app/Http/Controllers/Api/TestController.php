<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerarTestJob;
use App\Models\Documento;
use App\Models\Test;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->tests()->with('documento')->latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'documento_id' => 'required|exists:documento,id',
            'nivel' => 'required|string',
            'total' => 'required|integer|min:1',
            'prop_unica' => 'required|integer',
            'prop_multi' => 'required|integer',
            'prop_escribir' => 'required|integer',
            'min_opciones' => 'required|integer',
            'max_opciones' => 'required|integer',
            'input_user'    => 'nullable|string|max:500',
        ]);

        $documento = Documento::where('id', $request->documento_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $test = Test::create([
            'documento_id' => $documento->id,
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
}
