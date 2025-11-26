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
        Schema::create('plan_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->unique()->constrained('ventas')->onDelete('cascade')
                ->comment('Una venta tiene un único plan de pagos');
            $table->integer('cantidad_cuotas')->unsigned();
            $table->decimal('tasa_interes', 5, 2)->default(0);
            $table->integer('dia_vencimiento_mensual')->unsigned()
                ->comment('Ej: El día 5 de cada mes');
            $table->timestamps();
            
            // Índices
            $table->index('venta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_pagos');
    }
};
