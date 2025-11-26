<?php

use App\Enums\MetodoPago;
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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('restrict');
            $table->foreignId('cuota_id')->nullable()->constrained('cuotas')->onDelete('set null')
                ->comment('Null si es pago al contado o adelanto general');
            $table->timestamp('fecha_pago')->useCurrent();
            $table->decimal('monto_pagado', 10, 2);
            $table->string('metodo_pago');
            $table->string('referencia_comprobante')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('venta_id');
            $table->index('cuota_id');
            $table->index('fecha_pago');
            $table->index('metodo_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
