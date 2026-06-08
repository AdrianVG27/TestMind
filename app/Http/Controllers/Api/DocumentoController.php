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
        try {
            $query = Documento::where('isPublic', true);

            if ($request->has('nombre')) {
                $query->where('nombre', 'like', '%'.$request->nombre.'%');
            }

            if ($request->has('categoria_codigo')) {
                $query->where('categoria_codigo', $request->categoria_codigo);
            }

            $documentos = $query->latest()->paginate(20);

            return DocumentoResource::collection($documentos);

        } catch (\Exception $e) {
            Log::error('Error en DocumentoController - indexPublic: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.DocumentoController_indexPublic.500',
                'message' => 'Error al recuperar el repositorio de documentos públicos.',
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $documentos = $request->user()->documento()->latest()->get();

            return DocumentoResource::collection($documentos);

        } catch (\Exception $e) {
            Log::error('Error en DocumentoController - index: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.DocumentoController_index.500',
                'message' => 'Error al recuperar tus documentos personales.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:12288',
            'categoria_codigo' => 'required|exists:AUX_Categoria,codigo',
            'isPublic' => 'boolean',
        ]);

        try {
            $archivo = $request->file('pdf');
            $user = $request->user();

            $fechaHora = Carbon::now()->format('Y-m-d_H-i-s');
            $nombreUnico = "{$fechaHora}.pdf";

            $rutaCarpeta = "documentos/{$user->id}";

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
            Log::error('Error en DocumentoController - store: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.DocumentoController_store.500',
                'message' => 'No se pudo guardar el archivo físico en el servidor.',
            ], 500);
        }
    }

    public function show(Documento $documento)
    {
        try {
            if (! $this->isOwnerOrPublic($documento)) {
                return response()->json([
                    'error_key' => 'error.DocumentoController_show.403',
                    'message' => 'No tienes permiso para acceder a este documento privado.',
                ], 403);
            }

            return response()->json($documento);

        } catch (\Exception $e) {
            Log::error('Error en DocumentoController - show: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.DocumentoController_show.500',
                'message' => 'Error al recuperar la información del documento.',
            ], 500);
        }
    }

    public function update(Request $request, Documento $documento)
    {
        if (! $this->isOwnerOrPublic($documento)) {
            return response()->json([
                'error_key' => 'error.DocumentoController_update.403',
                'message' => 'No tienes permiso para editar este documento.',
            ], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        try {
            $documento->update($request->only('nombre'));

            return response()->json($documento);

        } catch (\Exception $e) {
            Log::error('Error en DocumentoController - update: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.DocumentoController_update.500',
                'message' => 'Error al actualizar el nombre del documento.',
            ], 500);
        }
    }

    public function destroy(Documento $documento)
    {
        if (! $this->isStrictOwner($documento)) {
            return response()->json([
                'error_key' => 'error.DocumentoController_destroy.403',
                'message' => 'No tienes permiso para eliminar este documento.',
            ], 403);
        }

        try {
            if (Storage::disk('local')->exists($documento->path)) {
                Storage::disk('local')->delete($documento->path);
            }

            $documento->delete();

            return response()->json(null, 204);

        } catch (\Exception $e) {
            Log::error('Error en DocumentoController - destroy: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.DocumentoController_destroy.500',
                'message' => 'Error al eliminar el documento del servidor.',
            ], 500);
        }
    }

    public function descargar(Documento $documento)
    {
        if (! $this->isOwnerOrPublic($documento)) {
            return response()->json([
                'error_key' => 'error.DocumentoController_descargar.403',
                'message' => 'No tienes permiso para descargar este documento.',
            ], 403);
        }

        try {
            $disk = Storage::disk('local');

            if (! $disk->exists($documento->path)) {
                return response()->json([
                    'error_key' => 'error.DocumentoController_descargar.404',
                    'message' => 'El archivo físico no se encuentra en el servidor.',
                ], 404);
            }

            $absolutePath = $disk->path($documento->path);

            return response()->file($absolutePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.basename($documento->path).'"',
            ]);

        } catch (\Exception $e) {
            Log::error('Error en DocumentoController - descargar: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.DocumentoController_descargar.500',
                'message' => 'Error interno al preparar la descarga del documento.',
            ], 500);
        }
    }

    private function isOwnerOrPublic(Documento $documento): bool
    {
        if ((bool) $documento->isPublic) {
            return true;
        }

        return $this->isStrictOwner($documento);
    }

    private function isStrictOwner(Documento $documento): bool
    {
        $user = auth('sanctum')->user();

        return $user && ((int) $documento->user_id === (int) $user->id);
    }
}
