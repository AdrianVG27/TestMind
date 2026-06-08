<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckTierLimits
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = $request->user();

            $tier = $user->tier;

            if (! $tier) {
                return response()->json([
                    'error_key' => 'error.CheckTierLimits_handle.403_no_tier',
                    'message' => 'El usuario no tiene ningún plan (tier) asociado.',
                ], 403);
            }

            $config = $tier->conf;

            $maxTests = (int) ($config['maxTests'] ?? 0);
            $maxPaginas = (int) ($config['maxPaginas'] ?? 0);
            $maxPreguntas = (int) ($config['maxPreguntas'] ?? 0);
            $maxExportaciones = (int) ($config['maxExportaciones'] ?? 0);

            $hace24Horas = Carbon::now()->subHours(24);

            if ($request->routeIs('test.store')) {
                $tests24h = $user->test()
                    ->where('test.created_at', '>=', $hace24Horas)
                    ->count();

                if ($tests24h >= $maxTests) {
                    return response()->json([
                        'error_key' => 'error.CheckTierLimits_handle.403_max_tests',
                        'error_params' => [
                            'plan' => $tier->codigo,
                            'maxTests' => $maxTests,
                        ],
                        'message' => "Límite de plan excedido. Tu nivel actual ({$tier->codigo}) solo permite generar {$maxTests} tests cada 24 horas.",
                    ], 403);
                }
            }

            if ($request->routeIs('documento.store')) {
                if ($request->hasFile('pdf')) {
                    $file = $request->file('pdf');

                    $pesoBytes = $file->getSize();

                    $paginasEstimadas = (int) ceil($pesoBytes / 10240);

                    if ($paginasEstimadas > $maxPaginas) {
                        return response()->json([
                            'error_key' => 'error.CheckTierLimits_handle.403_max_pages',
                            'error_params' => [
                                'plan' => $tier->codigo,
                                'maxPaginas' => $maxPaginas,
                                'pesoMaximoKB' => $maxPaginas * 10,
                                'paginasEstimadas' => $paginasEstimadas,
                            ],
                            'message' => "El documento excede el tamaño permitido. Tu plan ({$tier->codigo}) permite procesar PDFs de hasta {$maxPaginas} páginas.",
                        ], 403);
                    }
                }
            }

            if ($request->routeIs('test.store')) {
                $preguntasSolicitadas = (int) $request->input('total', 0);

                if ($preguntasSolicitadas > $maxPreguntas) {
                    return response()->json([
                        'error_key' => 'error.CheckTierLimits_handle.403_max_questions',
                        'error_params' => [
                            'plan' => $tier->codigo,
                            'maxPreguntas' => $maxPreguntas,
                            'solicitadas' => $preguntasSolicitadas,
                        ],
                        'message' => "Límite de plan excedido. Tu nivel actual ({$tier->codigo}) solo permite generar tests de hasta {$maxPreguntas} preguntas.",
                    ], 403);
                }
            }

            if ($request->is('api/user/test/*/exportar/moodle-gift')) {
                if ($maxExportaciones === 0) {
                    return response()->json([
                        'error_key' => 'error.CheckTierLimits_handle.403_max_exports',
                        'error_params' => [
                            'plan' => $tier->codigo,
                        ],
                        'message' => "Función Premium bloqueada. La exportación a formato Moodle GIFT no está permitida en el nivel {$tier->codigo}.",
                    ], 403);
                }
            }

            return $next($request);

        } catch (\Exception $e) {
            Log::error('Error crítico dentro del middleware CheckTierLimits: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.CheckTierLimits_handle.500',
                'message' => 'Error interno de la infraestructura de control de suscripciones.',
            ], 500);
        }
    }
}
