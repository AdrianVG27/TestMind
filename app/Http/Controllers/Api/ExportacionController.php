<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Services\MoodleGiftService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

            if (! is_array($preguntas)) {
                $preguntas = json_decode($preguntas, true);
            }

            if (empty($preguntas) || ! is_array($preguntas)) {
                return response()->json([
                    'error_key' => 'error.ExportacionController_exportarAMoodleGift.422',
                    'message' => 'El campo preguntas no contiene una colección de datos válida para la exportación.',
                ], 422);
            }

            $textoGift = $this->giftService->jsonToGift($preguntas);

            return response()->json([
                'message' => 'Exportación generada con éxito.',
                'data' => $textoGift,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error_key' => 'error.ExportacionController_exportarAMoodleGift.404',
                'message' => 'El cuestionario solicitado no existe en el sistema.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error exportando Test ID '.$id.' a GIFT: '.$e->getMessage());

            return response()->json([
                'error_key' => 'error.ExportacionController_exportarAMoodleGift.500',
                'message' => 'Ocurrió un error interno al generar el formato de exportación.',
            ], 500);
        }
    }
}
