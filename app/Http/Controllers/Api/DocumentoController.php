<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentoResource;
use App\Models\Documento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    public function indexPublic(Request $request)
    {
        $query = Documento::where('isPublic', true);

        if ($request->has('nombre')) {
            $query->where('nombre', 'like', '%'.$request->nombre.'%');
        }

        if ($request->has('categoria_codigo')) {
            $query->where('categoria_codigo', $request->categoria_codigo);
        }

        $documentos = $query->latest()->paginate(20);

        return DocumentoResource::collection($documentos);
    }

    /**
     * READ: Listar documentos del usuario autenticado.
     */
    public function index(Request $request)
    {
        $documentos = $request->user()->documento()->latest()->get();

        return DocumentoResource::collection($documentos);
    }

    /**
     * CREATE: Subir un PDF y guardarlo en storage/app/private.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:12288', // 12MB Máx
            'categoria_codigo' => 'required|exists:AUX_Categoria,codigo',
            'isPublic' => 'boolean',
        ]);

        $archivo = $request->file('pdf');
        $user = $request->user();

        $fechaHora = Carbon::now()->format('Y-m-d_H-i-s');
        $nombreUnico = "{$fechaHora}.pdf";

        $rutaCarpeta = "documentos/{$user->id}";

        try {
            $pathFinal = $archivo->storeAs($rutaCarpeta, $nombreUnico, 'local');

            $nombreOriginalConExtension = $archivo->getClientOriginalName();
            $nombreLimpioSinExtension = pathinfo($nombreOriginalConExtension, PATHINFO_FILENAME);

            $documento = Documento::create([
                'user_id' => $user->id,
                'categoria_codigo' => $request->categoria_codigo,
                'nombre' => $nombreLimpioSinExtension,
                'path' => $pathFinal,
                'isPublic' => $request->boolean('isPublic'),
            ]);

            return response()->json($documento, 201);

        } catch (\Exception $e) {
            Log::error($e);

            return response()->json(['error' => 'No se pudo guardar el archivo físico'], 500);
        }
    }

    /**
     * READ: Ver detalle de un documento específico.
     */
    public function show(Documento $documento)
    {
        if (! $documento->isPublic) {
            $this->authorizeOwner($documento);
        }

        return response()->json($documento);
    }

    /**
     * UPDATE: Cambiar el nombre "legible" del documento.
     */
    public function update(Request $request, Documento $documento)
    {
        $this->authorizeOwner($documento);

        $request->validate([
            'nombre' => 'required|string|max:255',
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

    public function descargar(Documento $documento)
    {
        $this->authorizeOwner($documento);

        $disk = Storage::disk('local');

        if (! $disk->exists($documento->path)) {
            return response()->json(['error' => 'Archivo no encontrado'], 404);
        }

        $absolutePath = $disk->path($documento->path);

        return response()->file($absolutePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.basename($documento->path).'"',
        ]);
    }

    /**
     * Validador de propiedad para seguridad.
     */
    private function authorizeOwner(Documento $documento)
    {
        $isPublic = (bool) $documento->isPublic;
        $ownerId = (int) $documento->user_id;

        if ($isPublic) {
            return;
        }

        $user = auth('sanctum')->user();

        if (! $user || $ownerId !== (int) $user->id) {
            abort(403, 'No tienes permiso para acceder a este documento privado.');
        }
    }
}
