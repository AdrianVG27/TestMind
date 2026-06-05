<?php

namespace App\Services;

class MoodleGiftService
{
    /**
     * Transforma el array plano de preguntas de TestMind al estándar Moodle GIFT.
     *
     * @param array $preguntas Colección estructurada de preguntas
     * @return string Bloque de texto plano formateado e inmune a errores de sintaxis
     */
    public function jsonToGift(array $preguntas): string
    {
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
            else {
                $respuestasCorrectasArray = array_map('trim', explode(',', $correcta));

                foreach ($opciones as $opcion) {
                    $opcionLimpia = str_replace('\\', '\\\\', trim($opcion));

                    $opcionLimpia = $this->escaparTextoGift($opcionLimpia);
                    
                    $opcionLimpiaConSpan = "<span>" . $opcionLimpia . "</span>";

                    $esCorrecta = ($tipo === 'multi_respuesta') 
                        ? in_array(trim($opcion), $respuestasCorrectasArray) 
                        : (trim($opcion) === $correcta);

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