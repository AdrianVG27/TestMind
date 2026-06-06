<?php

namespace App\Jobs;

use App\Models\Test; // Importante importar el modelo
use App\Services\GeminiService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerarTestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    public $tries = 3;

    public function backoff(): array
    {
        return [60, 120, 240]; // Primer reintento al minuto, segundo a los 2 min, tercero a los 4 min.
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Test $test
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GeminiService $gemini): void
    {
        $this->test->update(['estado_codigo' => 'EP']);

        try {
            $resultadoIA = $gemini->generarEstructuraTest(
                $this->test->documento->path,
                $this->test->configuracion
            );

            $this->test->update([
                'preguntas' => $resultadoIA['preguntas'] ?? $resultadoIA,
                'estado_codigo' => 'C',
            ]);

            Log::info("Test ID {$this->test->id} generado con éxito.");

        } catch (Exception $e) {
            Log::error("Error en GenerarTestJob para Test ID {$this->test->id}: ".$e->getMessage());

            if ($this->attempts() >= $this->tries) {
                $this->test->update(['estado_codigo' => 'E']);
            }

            throw $e;
        }
    }
}
