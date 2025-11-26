<?php

use App\Enums\EstadoCuota;
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
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_pago_id')->constrained('plan_pagos')->onDelete('cascade');
            $table->integer('numero_cuota')->unsigned()->comment('1, 2, 3...');
            $table->date('fecha_vencimiento');
            $table->decimal('monto_capital', 10, 2);
            $table->decimal('monto_interes', 10, 2);
            $table->decimal('monto_total_cuota', 10, 2);
            $table->string('estado_cuota')->default(EstadoCuota::PENDIENTE->value);
            $table->timestamps();
            
            // Índices
            $table->index('plan_pago_id');
            $table->index('fecha_vencimiento');
            $table->index('estado_cuota');
            $table->unique(['plan_pago_id', 'numero_cuota']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};
