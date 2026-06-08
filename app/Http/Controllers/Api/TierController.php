<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tier;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TierController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $planes = Tier::where('valorUsado', true)
                ->orderBy('id', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $planes,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Fallo al listar los Tiers públicos: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'error_key' => 'error.TierController_index.500',
                'message' => 'No se ha podido recuperar el catálogo de suscripciones.',
            ], 500);
        }
    }
}