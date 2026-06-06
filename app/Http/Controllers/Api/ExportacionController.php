<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MoodleGiftService;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExportacionController extends Controller
{
    protected $giftService;

    public function __construct(MoodleGiftService $giftService)
    {
        $this->giftService = $giftService;
    }

    public function exportarAMoodleGift(Request $request, $id)
    {
        try {
            $test = Test::findOrFail($id);

            $preguntas = $test->preguntas;

            if (!is_array($preguntas)) {
                $preguntas = json_decode($preguntas, true);
            }

            if (empty($preguntas) || !is_array($preguntas)) {
                return response()->json([
                    'error' => 'El campo preguntas no contiene una colección de datos válida para la exportación.'
                ], 422);
            }

            $textoGift = $this->giftService->jsonToGift($preguntas);

            return response()->json([
                'message' => 'Exportación generada con éxito.',
                'data' => $textoGift
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error exportando Test ID ' . $id . ' a GIFT: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Ocurrió un error interno al generar el formato de exportación.'
            ], 500);
        }
    }
}