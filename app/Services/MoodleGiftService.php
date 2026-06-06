<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class MoodleGiftService
{
    /**
     * Transforma el array plano de preguntas de TestMind al estándar Moodle GIFT.
     *
     * @param array $preguntas Colección estructurada de preguntas
     * @return string Bloque de texto plano formateado
     * @throws Exception
     */
    public function jsonToGift(array $preguntas): string
    {
        try {
            $giftContent = "";

            foreach ($preguntas as $index => $pregunta) {
                $enunciadoLimpio = $this->escaparTextoGift($pregunta['enunciado'] ?? '');
                
                $enunciadoConSpan = "<span>" . $enunciadoLimpio . "</span>";

                $opciones = $pregunta['opciones'] ?? [];
                $correcta = trim($pregunta['respuesta_correcta'] ?? '');
                $tipo = strtolower(trim($pregunta['tipo'] ?? ''));

                $giftContent .= "::Pregunta " . ($index + 1) . ":: " . $enunciadoConSpan . " {\n";

                if ($tipo === 'completar_escribir') {
                    $valorCorrecto = $this->escaparTextoGift($correcta);
                    $valorCorrectoConSpan = "<span>" . $valorCorrecto . "</span>";
                    
                    $giftContent .= "    =" . $valorCorrectoConSpan . "\n";
                } 
                else if ($tipo === 'multi_respuesta') {
                    $respuestasCorrectasArray = array_map('trim', explode(',', $correcta));
                    $totalCorrectas = count($respuestasCorrectasArray);
                    
                    $pesoPositivo = $totalCorrectas > 0 ? (100 / $totalCorrectas) : 100;
                    
                    $totalIncorrectas = count($opciones) - $totalCorrectas;
                    $pesoNegativo = $totalIncorrectas > 0 ? (100 / $totalIncorrectas) : 100;

                    foreach ($opciones as $opcion) {
                        $opcionLimpia = str_replace('\\', '\\\\', trim($opcion));
                        $opcionLimpia = $this->escaparTextoGift($opcionLimpia);
                        $opcionLimpiaConSpan = "<span>" . $opcionLimpia . "</span>";

                        $esCorrecta = in_array(trim($opcion), $respuestasCorrectasArray);

                        if ($esCorrecta) {
                            $giftContent .= "    ~%" . number_format($pesoPositivo, 4, '.', '') . "%" . $opcionLimpiaConSpan . "\n";
                        } else {
                            $giftContent .= "    ~%-" . number_format($pesoNegativo, 4, '.', '') . "%" . $opcionLimpiaConSpan . "\n";
                        }
                    }
                } 
                else {
                    foreach ($opciones as $opcion) {
                        $opcionLimpia = str_replace('\\', '\\\\', trim($opcion));
                        $opcionLimpia = $this->escaparTextoGift($opcionLimpia);
                        $opcionLimpiaConSpan = "<span>" . $opcionLimpia . "</span>";

                        $esCorrecta = (trim($opcion) === $correcta);

                        if ($esCorrecta) {
                            $giftContent .= "    =" . $opcionLimpiaConSpan . "\n";
                        } else {
                            $giftContent .= "    ~" . $opcionLimpiaConSpan . "\n";
                        }
                    }
                }

                $giftContent .= "}\n\n";
            }

            return rtrim($giftContent);

        } catch (Exception $e) {
            Log::error('Error de parseo dentro de MoodleGiftService - jsonToGift: ' . $e->getMessage());
            throw new Exception('Error interno al compilar la estructura al estándar Moodle GIFT.');
        }
    }

    private function escaparTextoGift(string $texto): string
    {
        $textoLimpio = trim($texto);

        if ($textoLimpio === '') {
            return '';
        }
        
        $caracteresBuscar = ['{', '}', '~', '=', '#', ':'];
        $caracteresReemplazar = ['\{', '\}', '\~', '\=', '\#', '\:'];

        return str_replace($caracteresBuscar, $caracteresReemplazar, $textoLimpio);
    }
}