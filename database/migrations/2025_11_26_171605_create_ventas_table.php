<?php

use App\Enums\EstadoPago;
use App\Enums\TipoPago;
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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('vendedor_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('viaje_id')->constrained('viajes')->onDelete('restrict');
            $table->timestamp('fecha_venta')->useCurrent();
            $table->decimal('monto_total', 10, 2);
            $table->string('tipo_pago')->default(TipoPago::CONTADO->value);
            $table->string('estado_pago')->default(EstadoPago::PENDIENTE->value);
            $table->timestamps();
            
            // Índices
            $table->index('cliente_id');
            $table->index('vendedor_id');
            $table->index('viaje_id');
            $table->index('fecha_venta');
            $table->index('estado_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
