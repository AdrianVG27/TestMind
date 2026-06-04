<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AUX_Interfaz_Traduccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lenguaje_id')->constrained('AUX_Lenguaje')->onDelete('cascade');
            $table->string('clave');
            $table->text('valor');
            $table->timestamps();

            $table->unique(['lenguaje_id', 'clave']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AUX_Interfaz_Traduccion');
    }
};