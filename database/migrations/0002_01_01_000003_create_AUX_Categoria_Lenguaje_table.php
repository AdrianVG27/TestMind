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
        Schema::create('AUX_Categoria_Lenguaje', function (Blueprint $table) {
            $table->foreignId('categoria_id')->constrained('AUX_Categoria')->onDelete('cascade');
            $table->foreignId('lenguaje_id')->constrained('AUX_Lenguaje')->onDelete('cascade');
            $table->primary(['categoria_id', 'lenguaje_id']);
            $table->string('descripcion');
            $table->timestamps();
            
            $table->index('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('AUX_Categoria_Lenguaje');
    }
};
