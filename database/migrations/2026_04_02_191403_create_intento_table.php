<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('intento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade');
            $table->foreignId('test_id')->constrained('test')->onDelete('cascade');
            $table->json('respuestas_usuario');
            $table->integer('aciertos');
            $table->integer('total_preguntas');
            $table->decimal('nota', 4, 2);
            $table->integer('duracion_segundos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intento');
    }
};
