<?php

use App\Enums\EstadoViaje;
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
        Schema::create('viajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_viaje_id')->constrained('plan_viajes')->onDelete('cascade');
            $table->date('fecha_salida');
            $table->date('fecha_retorno');
            $table->integer('cupos_totales')->unsigned();
            $table->integer('cupos_disponibles')->unsigned();
            $table->string('estado_viaje')->default(EstadoViaje::ABIERTO->value);
            $table->timestamps();
            
            // Índices
            $table->index('plan_viaje_id');
            $table->index('fecha_salida');
            $table->index('estado_viaje');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viajes');
    }
};
