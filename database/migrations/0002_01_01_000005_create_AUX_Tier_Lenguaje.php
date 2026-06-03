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
        Schema::create('AUX_Tier_Lenguaje', function (Blueprint $table) {
            $table->foreignId('tier_id')->constrained('AUX_Tier')->onDelete('cascade');
            $table->foreignId('lenguaje_id')->constrained('AUX_Lenguaje')->onDelete('cascade');
            $table->primary(['tier_id', 'lenguaje_id']);
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
        Schema::dropIfExists('AUX_Tier_Lenguaje');
    }
};
