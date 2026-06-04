<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TablaApoyo;
use App\Models\InterfazTraduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TablaApoyoController extends Controller
{
    public function indexTablas()
    {
        return response()->json(TablaApoyo::all());
    }

    public function readRows($id)
    {
        try {
            $tablaApoyo = TablaApoyo::findOrFail($id);
            $nombreTabla = $tablaApoyo->nombreTA;

            if (! Schema::hasTable($nombreTabla)) {
                return response()->json(['error' => "La tabla {$nombreTabla} no existe físicamente."], 404);
            }

            $rows = DB::table($nombreTabla)->orderBy('id', 'asc')->get();

            return response()->json([
                'tabla' => $nombreTabla,
                'descripcion' => $tablaApoyo->descripcion,
                'tieneLenguajes' => (bool) $tablaApoyo->tieneLenguajes,
                'registros' => $rows,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ReadRows: '.$e->getMessage());
            return response()->json(['error' => 'Error al leer la tabla de apoyo.'], 500);
        }
    }

    public function createRow(Request $request, $id)
    {
        try {
            $tablaApoyo = TablaApoyo::findOrFail($id);
            $nombreTabla = $tablaApoyo->nombreTA;

            $payloadAInsertar = $request->except(['id', 'created_at', 'updated_at']);

            if (empty($payloadAInsertar)) {
                return response()->json(['error' => 'No se han enviado datos válidos.'], 400);
            }

            if (array_key_exists('codigo', $payloadAInsertar)) {
                $codigo = trim($payloadAInsertar['codigo']);
                if (empty($codigo)) {
                    return response()->json(['error' => 'El campo CÓDIGO es obligatorio.'], 422);
                }
                if (DB::table($nombreTabla)->where('codigo', $codigo)->exists()) {
                    return response()->json(['error' => "El código '{$codigo}' ya existe."], 422);
                }
                $payloadAInsertar['codigo'] = $codigo;
            }

            if (Schema::hasColumn($nombreTabla, 'created_at')) { $payloadAInsertar['created_at'] = now(); }
            if (Schema::hasColumn($nombreTabla, 'updated_at')) { $payloadAInsertar['updated_at'] = now(); }

            $nuevoRegistro = DB::transaction(function () use ($nombreTabla, $payloadAInsertar) {
                $nuevoId = DB::table($nombreTabla)->insertGetId($payloadAInsertar);
                $registro = DB::table($nombreTabla)->where('id', $nuevoId)->first();

                if ($nombreTabla === 'AUX_Lenguaje') {
                    $clavesExistentes = InterfazTraduccion::distinct()->pluck('clave');

                    foreach ($clavesExistentes as $clave) {
                        InterfazTraduccion::create([
                            'lenguaje_id' => $nuevoId,
                            'clave' => $clave,
                            'valor' => ''
                        ]);
                    }
                }

                return $registro;
            });

            return response()->json(['message' => 'Creado con éxito.', 'data' => $nuevoRegistro], 201);
        } catch (\Exception $e) {
            Log::error('Error en CreateRow con gancho multiidioma: '.$e->getMessage());
            return response()->json(['error' => 'Error al insertar el registro maestro.'], 500);
        }
    }

    public function updateRow(Request $request, $id, $rowId)
    {
        try {
            $tablaApoyo = TablaApoyo::findOrFail($id);
            $nombreTabla = $tablaApoyo->nombreTA;

            $payloadAEditar = $request->except(['id', 'created_at', 'updated_at']);

            if (strtolower($nombreTabla) === 'tablaapoyo') {
                $filaActual = DB::table($nombreTabla)->where('id', $rowId)->first();
                
                if ($filaActual && strtolower($filaActual->nombreTA) === 'tablaapoyo') {
                    if (array_key_exists('nombreTA', $payloadAEditar) && trim($payloadAEditar['nombreTA']) !== $filaActual->nombreTA) {
                        return response()->json([
                            'error' => 'Operación denegada: No se permite alterar el nombre físico de la "TablaApoyo" primaria porque el sistema perdería su mapa relacional.'
                        ], 422);
                    }
                }
            }

            if (Schema::hasColumn($nombreTabla, 'updated_at')) { $payloadAEditar['updated_at'] = now(); }

            DB::table($nombreTabla)->where('id', $rowId)->update($payloadAEditar);
            $registroActualizado = DB::table($nombreTabla)->where('id', $rowId)->first();

            return response()->json(['message' => 'Actualizado con éxito.', 'data' => $registroActualizado]);
        } catch (\Exception $e) {
            Log::error('Error en UpdateRow Protegido: '.$e->getMessage());
            return response()->json(['error' => 'Error al actualizar el registro.'], 500);
        }
    }

    public function deleteRow($id, $rowId)
    {
        try {
            $tablaApoyo = TablaApoyo::findOrFail($id);
            $nombreTabla = $tablaApoyo->nombreTA;

            if (strtolower($nombreTabla) === 'tablaapoyo') {
                $filaABorrar = DB::table($nombreTabla)->where('id', $rowId)->first();
                
                if ($filaABorrar && strtolower($filaABorrar->nombreTA) === 'tablaapoyo') {
                    return response()->json([
                        'error' => 'Operación cancelada: No se permite eliminar el registro raíz de "TablaApoyo" porque destruiría la integridad del panel dinámico.'
                    ], 422);
                }
            }

            DB::transaction(function () use ($nombreTabla, $rowId) {
                if ($nombreTabla === 'AUX_Lenguaje') {
                    InterfazTraduccion::where('lenguaje_id', $rowId)->delete();
                }

                DB::table($nombreTabla)->where('id', $rowId)->delete();
            });

            return response()->json(null, 204);

        } catch (\Exception $e) {
            Log::error('Error en DeleteRow Protegido con cascada: '.$e->getMessage());
            return response()->json(['error' => 'No se pudo eliminar el registro debido a dependencias activas.'], 500);
        }
    }

    public function getRowLanguages($id, $rowId)
    {
        try {
            $tablaApoyo = TablaApoyo::findOrFail($id);
            $nombreTabla = $tablaApoyo->nombreTA;

            $tablaTraduccion = $nombreTabla.'_Lenguaje';
            $fkSugerida = strtolower(str_replace('AUX_', '', $nombreTabla)).'_id';

            if (! Schema::hasTable($tablaTraduccion)) {
                return response()->json(['error' => "La estructura multiidioma para {$nombreTabla} no existe."], 404);
            }

            $idiomasGlobales = DB::table('AUX_Lenguaje')->orderBy('id', 'asc')->get();

            $traduccionesExistentes = DB::table($tablaTraduccion)
                ->where($fkSugerida, $rowId)
                ->get()
                ->keyBy('lenguaje_id');

            $resultado = [];

            foreach ($idiomasGlobales as $idioma) {
                $existePivote = $traduccionesExistentes->has($idioma->id);

                $resultado[] = [
                    'padre_id' => (int) $rowId,
                    'lenguaje_id' => $idioma->id,
                    'lenguaje_codigo' => $idioma->codigo,
                    'descripcion' => $existePivote ? $traduccionesExistentes->get($idioma->id)->descripcion : '',
                    'existe_pivote' => $existePivote,
                ];
            }

            return response()->json($resultado);

        } catch (\Exception $e) {
            Log::error('Error dinámico en getRowLanguages: '.$e->getMessage());
            return response()->json(['error' => 'No se pudieron recuperar las traducciones.'], 500);
        }
    }

    public function updateRowLanguages(Request $request, $id, $rowId)
    {
        try {
            $tablaApoyo = TablaApoyo::findOrFail($id);
            $nombreTabla = $tablaApoyo->nombreTA;
            $tablaTraduccion = $nombreTabla.'_Lenguaje';
            $fkSugerida = strtolower(str_replace('AUX_', '', $nombreTabla)).'_id';

            $loteTraducciones = $request->input('traducciones', []);

            if (empty($loteTraducciones)) {
                return response()->json(['error' => 'No se han enviado traducciones.'], 400);
            }

            DB::transaction(function () use ($tablaTraduccion, $fkSugerida, $loteTraducciones, $rowId) {
                foreach ($loteTraducciones as $trad) {
                    $texto = isset($trad['descripcion']) ? trim($trad['descripcion']) : '';

                    if ($trad['existe_pivote']) {
                        DB::table($tablaTraduccion)
                            ->where($fkSugerida, $rowId)
                            ->where('lenguaje_id', $trad['lenguaje_id'])
                            ->update([
                                'descripcion' => $texto,
                                'updated_at' => now(),
                            ]);
                    } else {
                        if (! empty($texto)) {
                            DB::table($tablaTraduccion)->insert([
                                $fkSugerida => $rowId,
                                'lenguaje_id' => $trad['lenguaje_id'],
                                'descripcion' => $texto,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            });

            return response()->json(['message' => 'Catálogo multiidioma optimizado y guardado con éxito.']);

        } catch (\Exception $e) {
            Log::error('Error dinámico en updateRowLanguages: '.$e->getMessage());
            return response()->json(['error' => 'Error interno al persistir el lote idiomático.'], 500);
        }
    }
}