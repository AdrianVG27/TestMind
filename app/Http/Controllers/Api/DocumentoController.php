<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DocumentoController extends Controller
{
    /**
     * READ: Listar documentos del usuario autenticado.
     */
    public function index(Request $request)
    {
        return response()->json($request->user()->documentos()->latest()->get());
    }

    /**
     * CREATE: Subir un PDF y guardarlo en storage/app/private.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:12288', // 12MB Máx
        ]);

        $archivo = $request->file('pdf');
        $user = $request->user();

        $fechaHora = Carbon::now()->format('Y-m-d_H-i-s');
        $nombreUnico = "{$fechaHora}.pdf";

        $rutaCarpeta = "documentos/{$user->id}";

        try {
            $pathFinal = $archivo->storeAs($rutaCarpeta, $nombreUnico, 'local');

            $documento = Documento::create([
                'user_id' => $user->id,
                'nombre'  => $archivo->getClientOriginalName(),
                'path'    => $pathFinal,
            ]);

            return response()->json($documento, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo guardar el archivo físico'], 500);
        }
    }

    /**
     * READ: Ver detalle de un documento específico.
     */
    public function show(Documento $documento)
    {
        $this->authorizeOwner($documento);
        return response()->json($documento);
    }

    /**
     * UPDATE: Cambiar el nombre "legible" del documento.
     */
    public function update(Request $request, Documento $documento)
    {
        $this->authorizeOwner($documento);

        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        $documento->update($request->only('nombre'));

        return response()->json($documento);
    }

    /**
     * DELETE: Borrar registro y archivo físico.
     */
    public function destroy(Documento $documento)
    {
        $this->authorizeOwner($documento);

        // 1. Borrar el archivo físico del disco local
        if (Storage::disk('local')->exists($documento->path)) {
            Storage::disk('local')->delete($documento->path);
        }

        $documento->delete();

        return response()->json(null, 204);
    }

    /**
     * Validador de propiedad para seguridad.
     */
    private function authorizeOwner(Documento $documento)
    {
        if ($documento->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para acceder a este documento.');
        }
    }
}