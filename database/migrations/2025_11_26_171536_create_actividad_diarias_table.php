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
        Schema::create('actividad_diarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_viaje_id')->constrained('plan_viajes')->onDelete('cascade');
            $table->integer('dia_numero')->unsigned()->comment('Ej: Día 1, Día 2');
            $table->text('descripcion_actividad');
            $table->time('hora_inicio')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['plan_viaje_id', 'dia_numero']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_diarias');
    }
};
