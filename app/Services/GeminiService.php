<?php

namespace App\Services;

use Exception;
use Gemini\Data\Blob;
use Gemini\Data\Content;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\MimeType;
use Gemini\Enums\ResponseMimeType;
use Gemini\Laravel\Facades\Gemini as GeminiClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiService
{
    protected string $apiKey;

    protected string $model;

    protected float $temperature;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model');
        $this->temperature = config('services.gemini.temperature');

        if (empty($this->apiKey)) {
            Log::error('GeminiService: Falta la API Key en el archivo .env');
            throw new Exception('The Gemini API Key is missing. Revisa tu archivo .env');
        }
    }

    public function generarEstructuraTest(string $pdfPath, array $config): array
    {
        try {
            if (! Storage::disk('local')->exists($pdfPath)) {
                throw new Exception('No se encuentra el archivo en: '.Storage::disk('local')->path($pdfPath));
            }

            $generationConfig = new GenerationConfig(
                responseMimeType: ResponseMimeType::APPLICATION_JSON,
                responseSchema: $this->getEsquema(),
                temperature: $this->temperature,
            );

            $modelo = GeminiClient::generativeModel(model: $this->model)
                ->withSystemInstruction(Content::parse(
                    'Eres un profesor experto en informática. '.
                    "Es OBLIGATORIO que el array 'preguntas' tenga EXACTAMENTE la cantidad de elementos solicitada. ".
                    'No resumas, no omitas, no incluyas explicaciones y genera solo JSON puro.'
                ))
                ->withGenerationConfig($generationConfig);

            $promptUsuario = $this->prepararPrompt($config);

            $resultado = $modelo->generateContent([
                $promptUsuario,
                new Blob(
                    mimeType: MimeType::APPLICATION_PDF,
                    data: base64_encode(Storage::disk('local')->get($pdfPath))
                ),
            ]);

            $resText = $resultado->text();

            if (empty($resText)) {
                throw new Exception('La IA devolvió una respuesta vacía.');
            }

            return json_decode($resText, true);

        } catch (Exception $e) {
            Log::error('ERROR CRÍTICO GEMINI: '.$e->getMessage());
            throw $e;
        }
    }

    private function prepararPrompt(array $config): string
    {
        $prompt = "Genera un test técnico con las siguientes especificaciones ESTRICTAS:
                1. NIVEL: {$config['nivel']}.
                2. CANTIDAD TOTAL: {$config['total']} preguntas.
                3. DISTRIBUCIÓN DE TIPOS:
                - {$config['prop_unica']}% de 'unica_seleccion'.
                - {$config['prop_multi']}% de 'multi_respuesta'.
                - {$config['prop_escribir']}% de 'completar_escribir'.
                4. OPCIONES: Entre {$config['min_opciones']} y {$config['max_opciones']} por pregunta.
                
                Analiza el PDF adjunto y extrae los conceptos.";

        if (! empty($config['input_user'])) {
            $prompt .= "\n\nDIRECTRICES ESPECÍFICAS DEL USUARIO (Prioridad Alta):
                    {$config['input_user']}";
        } else {
            $prompt .= "\n\nNota: Asegúrate de variar los temas cubiertos en el PDF, evitando centrarte únicamente en los conceptos introductorios.";
        }

        return $prompt;
    }

    private function getEsquema(): Schema
    {
        return new Schema(
            type: DataType::OBJECT,
            properties: [
                'preguntas' => new Schema(
                    type: DataType::ARRAY,
                    items: new Schema(
                        type: DataType::OBJECT,
                        properties: [
                            'tipo' => new Schema(type: DataType::STRING),
                            'enunciado' => new Schema(type: DataType::STRING),
                            'opciones' => new Schema(
                                type: DataType::ARRAY,
                                items: new Schema(type: DataType::STRING)
                            ),
                            'respuesta_correcta' => new Schema(type: DataType::STRING),
                        ],
                        required: ['tipo', 'enunciado', 'opciones', 'respuesta_correcta']
                    )
                ),
            ],
            required: ['preguntas']
        );
    }
}
