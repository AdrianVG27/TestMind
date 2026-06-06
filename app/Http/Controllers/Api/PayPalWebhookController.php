<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        try {
            $payload = $request->all();
            $eventType = $payload['event_type'] ?? null;

            Log::info("PayPal Webhook Recibido: {$eventType}");

            switch ($eventType) {
                case 'BILLING.PLAN.CREATED':
                case 'BILLING.PLAN.ACTIVATED':
                    return $this->activarTierEnCatalogo($payload);

                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                    return $this->activarSuscripcion($payload);

                case 'BILLING.SUBSCRIPTION.CANCELLED':
                    return $this->registrarCancelacionAsincrona($payload);

                case 'BILLING.SUBSCRIPTION.EXPIRED':
                    return $this->degradarSuscripciónExpirada($payload);

                default:
                    return response()->json(['message' => 'Evento ignorado de forma controlada.'], 200);
            }
        } catch (\Exception $e) {
            Log::error('Fallo estructural procesando Webhook de PayPal: ' . $e->getMessage());
            return response()->json([
                'error_key' => 'error.PayPalWebhookController_handleWebhook.500',
                'message' => 'Fallo crítico interno en el procesador de pasarela.'
            ], 500);
        }
    }

    private function activarTierEnCatalogo(array $payload)
    {
        try {
            $resource = $payload['resource'];
            $paypalPlanId = $resource['id'];
            $name = $resource['name'];

            $codigoTier = trim(str_replace('TestMind', '', $name));

            $tier = Tier::where('codigo', $codigoTier)->first();

            if (! $tier) {
                Log::error("Webhook Catálogo: No se encontró el Tier local para el código '{$codigoTier}'");
                return response()->json([
                    'error_key' => 'error.PayPalWebhookController_activarTierEnCatalogo.422',
                    'message' => 'Tier de catálogo no mapeado localmente.'
                ], 422);
            }

            $tier->update([
                'paypal_id' => $paypalPlanId,
                'valorUsado' => true,
            ]);

            Log::info("Catálogo TA: El Tier '{$codigoTier}' ha sido validado por PayPal y se ha activado en la columna dedicada.");

            return response()->json(['message' => 'Tier activado en catálogo con éxito.'], 200);
        } catch (\Exception $e) {
            Log::error('Error en activarTierEnCatalogo: ' . $e->getMessage());
            return response()->json(['error' => 'Fallo al actualizar catálogo.'], 500);
        }
    }

    private function activarSuscripcion(array $payload)
    {
        try {
            $resource = $payload['resource'];
            $subscriptionId = $resource['id'];
            $paypalPlanId = $resource['plan_id'];
            $status = $resource['status'];

            $tierCorrespondiente = Tier::where('paypal_id', $paypalPlanId)->first();

            if (! $tierCorrespondiente) {
                Log::error("Webhook Error: Llegó un pago para el plan de PayPal '{$paypalPlanId}' pero no está registrado en tu columna paypal_id.");
                return response()->json([
                    'error_key' => 'error.PayPalWebhookController_activarSuscripcion.422',
                    'message' => 'Plan de PayPal no mapeado en base de datos.'
                ], 422);
            }

            $tierCodigo = $tierCorrespondiente->codigo;
            $user = User::where('paypal_subscription_id', $subscriptionId)->first();

            if (! $user && isset($resource['subscriber']['email_address'])) {
                $paypalEmail = $resource['subscriber']['email_address'];
                $user = User::where('email', $paypalEmail)->first();
            }

            if (! $user) {
                Log::error("Webhook Error: Imposible asociar el cobro {$subscriptionId}. Ningún usuario coincide con el ID de suscripción ni con el correo.");
                return response()->json([
                    'error_key' => 'error.PayPalWebhookController_activarSuscripcion.404',
                    'message' => 'Usuario inlocalizable.'
                ], 404);
            }

            $user->update([
                'tier_codigo' => $tierCodigo,
                'paypal_subscription_id' => $subscriptionId,
                'paypal_status' => $status,
                'subscription_ends_at' => now()->addMonth(),
            ]);

            Log::info("Finanzas: El alumno {$user->email} ha sido ascendido dinámicamente al nivel [{$tierCodigo}] tras confirmar el pago de la suscripción {$subscriptionId}.");

            return response()->json(['message' => 'Suscripción procesada y usuario ascendido con éxito.'], 200);
        } catch (\Exception $e) {
            Log::error('Error en activarSuscripcion: ' . $e->getMessage());
            return response()->json(['error' => 'Fallo al procesar alta de suscripción.'], 500);
        }
    }

    private function registrarCancelacionAsincrona(array $payload)
    {
        try {
            $resource = $payload['resource'];
            $subscriptionId = $resource['id'];

            $user = User::where('paypal_subscription_id', $subscriptionId)->first();

            if (! $user) {
                Log::error("Webhook Error (Cancelación Externa): No se encontró al usuario de la suscripción {$subscriptionId}");
                return response()->json([
                    'error_key' => 'error.PayPalWebhookController_registrarCancelacionAsincrona.404',
                    'message' => 'Usuario no encontrado.'
                ], 404);
            }

            $user->update([
                'paypal_status' => 'CANCELLED',
                'subscription_ends_at' => $user->subscription_ends_at ?? now()->addMonth(),
            ]);

            Log::info("Finanzas: Cancelación externa detectada para {$subscriptionId}. El usuario {$user->email} mantiene sus ventajas hasta {$user->subscription_ends_at}.");

            return response()->json(['message' => 'Cancelación registrada de forma diferida.'], 200);
        } catch (\Exception $e) {
            Log::error('Error en registrarCancelacionAsincrona: ' . $e->getMessage());
            return response()->json(['error' => 'Fallo al cancelar diferido.'], 500);
        }
    }

    private function degradarSuscripciónExpirada(array $payload)
    {
        try {
            $resource = $payload['resource'];
            $subscriptionId = $resource['id'];

            $user = User::where('paypal_subscription_id', $subscriptionId)->first();

            if (! $user) {
                Log::error("Webhook Error (Expiración): No se encontró al usuario de la suscripción {$subscriptionId}");
                return response()->json([
                    'error_key' => 'error.PayPalWebhookController_degradarSuscripcionExpirada.404',
                    'message' => 'Usuario no encontrado.'
                ], 404);
            }

            $user->update([
                'tier_codigo' => 'FREE',
                'paypal_subscription_id' => null,
                'paypal_status' => 'EXPIRED',
                'subscription_ends_at' => null,
            ]);

            Log::info("Finanzas: El tiempo pagado de la suscripción {$subscriptionId} ha expirado. El usuario {$user->email} vuelve a nivel FREE.");

            return response()->json(['message' => 'Usuario degradado al plan libre de forma exitosa.'], 200);
        } catch (\Exception $e) {
            Log::error('Error en degradarSuscripciónExpirada: ' . $e->getMessage());
            return response()->json(['error' => 'Fallo al expirar suscripción.'], 500);
        }
    }

    public function vincularSuscripcion(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|string',
        ]);

        try {
            $user = $request->user();

            $user->update([
                'paypal_subscription_id' => $request->input('subscription_id'),
                'paypal_status' => 'PENDING',
            ]);

            return response()->json([
                'message' => 'Suscripción pre-vinculada con éxito. Esperando confirmación asíncrona de PayPal.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en vincularSuscripcion: ' . $e->getMessage());
            return response()->json([
                'error_key' => 'error.PayPalWebhookController_vincularSuscripcion.500',
                'message' => 'Error interno al pre-vincular la suscripción.'
            ], 500);
        }
    }

    public function cancelarSuscripcionActiva(Request $request)
    {
        try {
            $user = $request->user();
            $subscriptionId = $user->paypal_subscription_id;

            if (! $subscriptionId) {
                return response()->json([
                    'error_key' => 'error.PayPalWebhookController_cancelarSuscripcionActiva.422',
                    'message' => 'No se localizó ninguna suscripción activa para este perfil.'
                ], 422);
            }

            $clientId = env('PAYPAL_SANDBOX_CLIENT_ID');
            $secret = env('PAYPAL_SANDBOX_SECRET');

            $tokenResponse = Http::asForm()
                ->withBasicAuth($clientId, $secret)
                ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if ($tokenResponse->failed()) {
                Log::error('PayPal Cancel API Auth: Error de credenciales al tramitar baja voluntaria.');
                return response()->json([
                    'error_key' => 'error.PayPalWebhookController_cancelarSuscripcionActiva.502',
                    'message' => 'Fallo de conexión externa con la pasarela.'
                ], 502);
            }

            $accessToken = $tokenResponse->json()['access_token'];

            $paypalResponse = Http::withToken($accessToken)
                ->post("https://api-m.sandbox.paypal.com/v1/billing/subscriptions/{$subscriptionId}/cancel", [
                    'reason' => 'Cancelación voluntaria gestionada desde la interfaz de TestMind.',
                ]);

            if ($paypalResponse->failed() && $paypalResponse->status() !== 244) {
                Log::error("PayPal API Cancel Failure [Subs: {$subscriptionId}]: ".$paypalResponse->body());
                return response()->json([
                    'error_key' => 'error.PayPalWebhookController_cancelarSuscripcionActiva.400',
                    'message' => 'PayPal rechazó la solicitud de baja voluntaria.'
                ], 400);
            }

            $user->update([
                'paypal_status' => 'CANCELLED',
                'subscription_ends_at' => $user->subscription_ends_at ?? now()->addMonth(),
            ]);

            Log::info("Finanzas: El usuario {$user->email} ha cancelado su suscripción {$subscriptionId} pero retiene ventajas de forma diferida.");

            return response()->json([
                'message' => 'Tu renovación automática ha sido cancelada. Mantendrás tus privilegios de acceso hasta que termine el periodo de facturación actual.',
            ], 200);

        } catch (\Exception $e) {
            Log::error("Excepción crítica al tramitar la baja del usuario: ".$e->getMessage());
            return response()->json([
                'error_key' => 'error.PayPalWebhookController_cancelarSuscripcionActiva.500',
                'message' => 'Error interno de servidor al tramitar la baja voluntaria.'
            ], 500);
        }
    }
}