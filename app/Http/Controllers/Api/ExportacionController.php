<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MoodleGiftService;
use App\Models\Test;
use Illuminate\Http\Request;

class ExportacionController extends Controller
{
    protected $giftService;

    public function __construct(MoodleGiftService $giftService)
    {
        $this->giftService = $giftService;
    }

    public function exportarAMoodleGift(Request $request, $id)
    {
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

        $slugTitulo = str_replace(' ', '_', strtolower(preg_replace('/[^A-Za-z0-9 ]/', '', $test->titulo)));
        $nombreArchivo = 'moodle_gift_' . ($slugTitulo ?: 'test_' . $test->id) . '_' . now()->format('Ymd_His') . '.txt';

        return response($textoGift, 200)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"');
    }
}