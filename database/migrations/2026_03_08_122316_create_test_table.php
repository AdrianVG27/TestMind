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
        Schema::create('test', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')->constrained('documento')->onDelete('cascade');
            $table->string('estado_codigo');
            $table->foreign('estado_codigo')->references('codigo')->on('AUX_Estado')->onDelete('restrict');
            $table->string('titulo');
            $table->json('configuracion')->nullable();
            $table->json('preguntas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test');
    }
};
