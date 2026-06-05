<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InterfazTraduccion;
use App\Models\TablaApoyo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
                $tablaApoyo->delete();
                $e = "La tabla {$nombreTabla} no existe físicamente.";
                Log::error($e);
                return response()->json(['error' => $e], 404);
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

            $dispararPayPal = false;
            $precioPlan = 0;

            if ($nombreTabla === 'AUX_Tier') {
                $conf = is_string($payloadAInsertar['conf'])
                    ? json_decode($payloadAInsertar['conf'], true)
                    : $payloadAInsertar['conf'];

                $precioPlan = $conf['precio'] ?? 0;

                $conf['paypalPlanId'] = null;
                $payloadAInsertar['conf'] = json_encode($conf);

                $payloadAInsertar['valorUsado'] = false;

                if ($precioPlan > 0) {
                    $dispararPayPal = true;
                } else {
                    $payloadAInsertar['valorUsado'] = true;
                }
            }

            if (Schema::hasColumn($nombreTabla, 'created_at')) {
                $payloadAInsertar['created_at'] = now();
            }
            if (Schema::hasColumn($nombreTabla, 'updated_at')) {
                $payloadAInsertar['updated_at'] = now();
            }

            $nuevoRegistro = DB::transaction(function () use ($nombreTabla, $payloadAInsertar) {
                $nuevoId = DB::table($nombreTabla)->insertGetId($payloadAInsertar);
                $registro = DB::table($nombreTabla)->where('id', $nuevoId)->first();

                if ($nombreTabla === 'AUX_Lenguaje') {
                    $clavesExistentes = InterfazTraduccion::distinct()->pluck('clave');

                    foreach ($clavesExistentes as $clave) {
                        InterfazTraduccion::create([
                            'lenguaje_id' => $nuevoId,
                            'clave' => $clave,
                            'valor' => '',
                        ]);
                    }
                }

                return $registro;
            });

            if ($dispararPayPal) {
                dispatch(function () use ($payloadAInsertar, $precioPlan) {
                    $this->enviarPlanAPayPalSandbox($payloadAInsertar['codigo'], $precioPlan);
                })->afterResponse();
            }

            return response()->json(['message' => 'Creado con éxito en estado pendiente.', 'data' => $nuevoRegistro], 201);
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
            $desactivarPlanIdEnPayPal = null;

            if ($nombreTabla === 'AUX_Tier') {
                $filaActual = DB::table($nombreTabla)->where('id', $rowId)->first();

                if (array_key_exists('valorUsado', $payloadAEditar)) {
                    if ((bool) $payloadAEditar['valorUsado'] !== (bool) $filaActual->valorUsado) {
                        return response()->json([
                            'error' => 'Operación denegada: El estado "valorUsado" en los niveles de suscripción está gestionado automáticamente por los Webhooks de PayPal.',
                        ], 422);
                    }
                }

                if (array_key_exists('conf', $payloadAEditar) && $filaActual) {
                    $confActual = json_decode($filaActual->conf, true);
                    if (! empty($confActual['paypalPlanId'])) {
                        $desactivarPlanIdEnPayPal = $confActual['paypalPlanId'];

                        $payloadAEditar['valorUsado'] = false;
                    }
                }
            }

            if (strtolower($nombreTabla) === 'tablaapoyo') {
                $filaActual = DB::table($nombreTabla)->where('id', $rowId)->first();

                if ($filaActual && strtolower($filaActual->nombreTA) === 'tablaapoyo') {
                    if (array_key_exists('nombreTA', $payloadAEditar) && trim($payloadAEditar['nombreTA']) !== $filaActual->nombreTA) {
                        return response()->json([
                            'error' => 'Operación denegada: No se permite alterar el nombre físico de la "TablaApoyo" primaria porque el sistema perdería su mapa relacional.',
                        ], 422);
                    }
                }
            }

            if (Schema::hasColumn($nombreTabla, 'updated_at')) {
                $payloadAEditar['updated_at'] = now();
            }

            DB::table($nombreTabla)->where('id', $rowId)->update($payloadAEditar);
            $registroActualizado = DB::table($nombreTabla)->where('id', $rowId)->first();

            if ($desactivarPlanIdEnPayPal) {
                dispatch(function () use ($desactivarPlanIdEnPayPal) {
                    $this->desactivarPlanEnPayPalSandbox($desactivarPlanIdEnPayPal);
                })->afterResponse();
            }

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
            $planIdADesactivar = null;

            if ($nombreTabla === 'AUX_Tier') {
                $filaABorrar = DB::table($nombreTabla)->where('id', $rowId)->first();
                if ($filaABorrar) {
                    $conf = json_decode($filaABorrar->conf, true);
                    if (! empty($conf['paypalPlanId'])) {
                        $planIdADesactivar = $conf['paypalPlanId'];
                    }
                }
            }

            if (strtolower($nombreTabla) === 'tablaapoyo') {
                $filaABorrar = DB::table($nombreTabla)->where('id', $rowId)->first();

                if ($filaABorrar && strtolower($filaABorrar->nombreTA) === 'tablaapoyo') {
                    return response()->json([
                        'error' => 'Operación cancelada: No se permite eliminar el registro raíz de "TablaApoyo" porque destruiría la integridad del panel dinámico.',
                    ], 422);
                }
            }

            DB::transaction(function () use ($nombreTabla, $rowId) {
                if ($nombreTabla === 'AUX_Lenguaje') {
                    InterfazTraduccion::where('lenguaje_id', $rowId)->delete();
                }

                DB::table($nombreTabla)->where('id', $rowId)->delete();
            });

            if ($planIdADesactivar) {
                dispatch(function () use ($planIdADesactivar) {
                    $this->desactivarPlanEnPayPalSandbox($planIdADesactivar);
                })->afterResponse();
            }

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

    private function enviarPlanAPayPalSandbox(string $codigoPlan, $precio): void
    {
        try {
            $clientId = env('PAYPAL_SANDBOX_CLIENT_ID');
            $secret = env('PAYPAL_SANDBOX_SECRET');
            $productId = env('PAYPAL_PRODUCT_ID', 'TestMind');

            $tokenResponse = Http::asForm()
                ->withBasicAuth($clientId, $secret)
                ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if ($tokenResponse->failed()) {
                Log::error("PayPal Async Auth: No se pudo obtener el token para el plan {$codigoPlan}.");

                return;
            }

            $accessToken = $tokenResponse->json()['access_token'];

            Http::withToken($accessToken)
                ->post('https://api-m.sandbox.paypal.com/v1/catalogs/products', [
                    'id' => $productId,
                    'name' => 'Plataforma TestMind',
                    'type' => 'SERVICE',
                    'category' => 'SOFTWARE',
                ]);

            $planResponse = Http::withToken($accessToken)
                ->post('https://api-m.sandbox.paypal.com/v1/billing/plans', [
                    'product_id' => $productId,
                    'name' => 'TestMind '.$codigoPlan,
                    'description' => 'Acceso dinámico nivel '.$codigoPlan,
                    'status' => 'ACTIVE',
                    'billing_cycles' => [
                        [
                            'frequency' => [
                                'interval_unit' => 'MONTH',
                                'interval_count' => 1,
                            ],
                            'tenure_type' => 'REGULAR',
                            'sequence' => 1,
                            'total_cycles' => 0,
                            'pricing_scheme' => [
                                'fixed_price' => [
                                    'value' => number_format($precio, 2, '.', ''),
                                    'currency_code' => 'EUR',
                                ],
                            ],
                        ],
                    ],
                    'payment_preferences' => [
                        'auto_bill_outstanding' => true,
                        'setup_fee_failure_action' => 'CONTINUE',
                        'payment_failure_threshold' => 3,
                    ],
                ]);

            if ($planResponse->failed()) {
                Log::error("PayPal Async Create Plan Failure [{$codigoPlan}]: ".$planResponse->body());

                return;
            }

            $planData = $planResponse->json();
            $paypalPlanId = $planData['id'] ?? null;

            if ($paypalPlanId) {
                \App\Models\Tier::where('codigo', $codigoPlan)->update([
                    'paypal_id' => $paypalPlanId,
                    'valorUsado' => true,
                ]);

                Log::info("PayPal Redundancy Success: El Tier '{$codigoPlan}' ha sido enlazado directamente con el ID [{$paypalPlanId}] en background.");
            }

            Log::info("PayPal Async Success: Solicitud enviada para el Tier '{$codigoPlan}'. El Webhook confirmará la transacción cuando Sandbox procese la cola.");

        } catch (\Exception $e) {
            Log::error("Excepción en hilo asíncrono de PayPal para el Tier {$codigoPlan}: ".$e->getMessage());
        }
    }

    private function desactivarPlanEnPayPalSandbox(string $paypalPlanId): void
    {
        try {
            $clientId = env('PAYPAL_SANDBOX_CLIENT_ID');
            $secret = env('PAYPAL_SANDBOX_SECRET');

            $tokenResponse = Http::asForm()
                ->withBasicAuth($clientId, $secret)
                ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if ($tokenResponse->failed()) {
                Log::error("PayPal Async Deactivate Auth: Fallo al autenticar para dar de baja el plan {$paypalPlanId}.");

                return;
            }

            $accessToken = $tokenResponse->json()['access_token'];

            $deactivateResponse = Http::withToken($accessToken)
                ->post("https://api-m.sandbox.paypal.com/v1/billing/plans/{$paypalPlanId}/deactivate");

            if ($deactivateResponse->failed()) {
                Log::error("PayPal API Deactivate Failure [Plan: {$paypalPlanId}]: ".$deactivateResponse->body());

                return;
            }

            \App\Models\Tier::where('paypal_id', $paypalPlanId)->update([
                'valorUsado' => false,
            ]);

            Log::info("PayPal Async Success: El plan de suscripción '{$paypalPlanId}' ha sido marcado como INACTIVE y actualizado localmente.");

        } catch (\Exception $e) {
            Log::error("Excepción crítica al desactivar el plan {$paypalPlanId} en PayPal: ".$e->getMessage());
        }
    }
}
