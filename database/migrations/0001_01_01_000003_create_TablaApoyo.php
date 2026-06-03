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
        Schema::create('TablaApoyo', function (Blueprint $table) {
            $table->id();
            $table->string('nombreTA')->unique();
            $table->string('descripcion');
            $table->boolean('tieneLenguajes')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('TablaApoyo');
    }
};
