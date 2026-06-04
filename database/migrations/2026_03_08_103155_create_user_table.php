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
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('tier_codigo')->default('FREE');
            $table->foreign('tier_codigo')
                  ->references('codigo')
                  ->on('AUX_Tier')
                  ->onDelete('restrict');
            $table->string('name');
            $table->string('nickname')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();

            $table->string('paypal_subscription_id')->nullable()->unique();
            $table->string('paypal_status')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();

            $table->timestamps();

            $table->index(['paypal_subscription_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
