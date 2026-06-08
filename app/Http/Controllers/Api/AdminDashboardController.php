<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    public function userSegmentation()
    {
        try {
            $usersWithTiers = User::with(['tier.lenguajeActual'])
                ->get()
                ->groupBy('tier_codigo');

            $labels = [];
            $data = [];

            foreach ($usersWithTiers as $tierCodigo => $users) {
                $firstUser = $users->first();

                $labelTraducido = $firstUser && $firstUser->tier
                    ? $firstUser->tier->descripcion
                    : ($tierCodigo ?? 'SIN PLAN');

                $labels[] = $labelTraducido;
                $data[] = $users->count();
            }

            return response()->json([
                'labels' => $labels,
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en TestMind Admin - userSegmentation: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.AdminDashboardController_userSegmentation.500',
                'message' => 'Error al recuperar las métricas de segmentación de membresías.',
            ], 500);
        }
    }

    public function testsCreadosTimeline()
    {
        try {
            $historicoRaw = Test::select([
                DB::raw('YEAR(created_at) as anio'),
                DB::raw('MONTH(created_at) as mes'),
                DB::raw('COUNT(id) as total'),
            ])
                ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                ->orderBy('anio', 'asc')
                ->orderBy('mes', 'asc')
                ->get();

            $mesesEspanol = [
                1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
                5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
                9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
            ];

            $labels = [];
            $data = [];

            foreach ($historicoRaw as $registro) {
                $nombreMes = $mesesEspanol[$registro->mes] ?? 'Desconocido';

                $labels[] = "{$nombreMes} {$registro->anio}";
                $data[] = (int) $registro->total;
            }

            return response()->json([
                'labels' => $labels,
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en TestMind Admin - testsCreadosTimeline: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.AdminDashboardController_testsCreadosTimeline.500',
                'message' => 'Fallo al procesar la línea temporal de actividad de contenidos.',
            ], 500);
        }
    }

    public function testsByCategory()
    {
        try {
            $sessionLocale = request()->user()?->currentAccessToken()?->language;
            if (!$sessionLocale) {
                $sessionLocale = request()->header('language', config('app.locale'));
            }

            $resultados = Test::join('documento', 'test.documento_id', '=', 'documento.id')
                ->join('AUX_Categoria_Lenguaje', 'documento.id', '=', 'AUX_Categoria_Lenguaje.categoria_id')
                ->join('AUX_Lenguaje', 'AUX_Categoria_Lenguaje.lenguaje_id', '=', 'AUX_Lenguaje.id')
                ->where('AUX_Lenguaje.codigo', '=', $sessionLocale)
                ->select([
                    'AUX_Categoria_Lenguaje.descripcion as categoria',
                    DB::raw('COUNT(test.id) as total'),
                ])
                ->groupBy('AUX_Categoria_Lenguaje.descripcion')
                ->get();

            $labels = [];
            $data = [];

            foreach ($resultados as $row) {
                $labels[] = $row->categoria;
                $data[] = (int) $row->total;
            }

            return response()->json([
                'labels' => $labels,
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en AdminDashboardController - testsByCategory: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.AdminDashboardController_testsByCategory.500',
                'message' => 'Fallo al procesar el reparto analítico por categorías.'
            ], 500);
        }
    }
}