<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lenguaje;
use App\Models\InterfazTraduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InterfaceTranslationController extends Controller
{
    public function getJson($locale)
    {
        try {
            $lenguaje = Lenguaje::where('codigo', $locale)->first();

            if (!$lenguaje) {
                return response()->json([], 404);
            }

            $traducciones = InterfazTraduccion::where('lenguaje_id', $lenguaje->id)
                ->pluck('valor', 'clave')
                ->toArray();

            $jsonEstructurado = [];
            foreach ($traducciones as $clave => $valor) {
                data_set($jsonEstructurado, $clave, $valor);
            }

            return response()->json($jsonEstructurado);

        } catch (\Exception $e) {
            Log::error("Error crítico sirviendo JSON de Transloco ({$locale}): " . $e->getMessage());
            return response()->json(['error' => 'No se pudo compilar el archivo de idioma.'], 500);
        }
    }

    public function index()
    {
        try {
            $traducciones = InterfazTraduccion::with('lenguaje')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'clave' => $item->clave,
                        'valor' => $item->valor,
                        'lenguaje_codigo' => $item->lenguaje?->codigo
                    ];
                });

            return response()->json($traducciones);

        } catch (\Exception $e) {
            Log::error('Error en InterfaceTranslationController - index: ' . $e->getMessage());
            return response()->json(['error' => 'Error al recuperar el catálogo de literales.'], 500);
        }
    }

    public function updateKey(Request $request)
    {
        $request->validate([
            'clave' => 'required|string',
            'lenguaje_codigo' => 'required|string|exists:AUX_Lenguaje,codigo',
            'valor' => 'required|string'
        ]);

        $claveLimpia = trim($request->clave);

        DB::beginTransaction();

        try {
            $lenguajeObjetivo = Lenguaje::where('codigo', $request->lenguaje_codigo)->firstOrFail();

            $existeClave = InterfazTraduccion::where('clave', $claveLimpia)->exists();

            $traduccionOriginal = InterfazTraduccion::updateOrCreate(
                ['lenguaje_id' => $lenguajeObjetivo->id, 'clave' => $claveLimpia],
                ['valor' => $request->valor]
            );

            if (!$existeClave) {
                $otrosLenguajes = Lenguaje::where('id', '!=', $lenguajeObjetivo->id)->get();

                foreach ($otrosLenguajes as $lenguaje) {
                    InterfazTraduccion::create([
                        'lenguaje_id' => $lenguaje->id,
                        'clave' => $claveLimpia,
                        'valor' => ''
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Literal de la interfaz sincronizado y propagado con éxito.',
                'data' => [
                    'id' => $traduccionOriginal->id,
                    'clave' => $traduccionOriginal->clave,
                    'valor' => $traduccionOriginal->valor
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en InterfaceTranslationController - updateKey: ' . $e->getMessage());
            return response()->json(['error' => 'Fallo al sincronizar e instanciar la clave en los diccionarios.'], 500);
        }
    }

    public function destroyKey(Request $request)
    {
        $request->validate([
            'clave' => 'required|string'
        ]);

        try {
            $filasBorradas = InterfazTraduccion::where('clave', trim($request->clave))->delete();

            if ($filasBorradas === 0) {
                return response()->json(['error' => 'La clave especificada no existe.'], 404);
            }

            return response()->json([
                'message' => "La clave '{$request->clave}' ha sido purgada globalmente del sistema ({$filasBorradas} entradas eliminadas)."
            ]);

        } catch (\Exception $e) {
            Log::error('Error en InterfaceTranslationController - destroyKey: ' . $e->getMessage());
            return response()->json(['error' => 'Error de consistencia al purgar el literal.'], 500);
        }
    }

    public function destroyLanguage($id)
    {
        DB::beginTransaction();

        try {
            $lenguaje = Lenguaje::findOrFail($id);

            $traduccionesBorradas = InterfazTraduccion::where('lenguaje_id', $lenguaje->id)->delete();

            if (method_exists($lenguaje, 'tiers')) {
                $lenguaje->tiers()->detach();
            }
            if (method_exists($lenguaje, 'categorias')) {
                $lenguaje->categorias()->detach();
            }

            $lenguaje->delete();

            DB::commit();

            return response()->json([
                'message' => "Idioma '{$lenguaje->descripcion}' eliminado. Se han purgado {$traduccionesBorradas} literales de interfaz."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en InterfaceTranslationController - destroyLanguage: ' . $e->getMessage());
            return response()->json(['error' => 'Fallo al eliminar el idioma y sus diccionarios vinculados.'], 500);
        }
    }
}