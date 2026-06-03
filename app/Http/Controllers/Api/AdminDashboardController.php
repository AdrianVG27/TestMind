<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
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
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error en TestMind Admin - userSegmentation: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al recuperar las métricas de segmentación de membresías.',
                'codigo' => 'ERR_ADMIN_SEG_01'
            ], 500);
        }
    }
}
