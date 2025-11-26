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
        Schema::create('plan_viajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destino_id')->constrained('destinos')->onDelete('cascade');
            $table->string('nombre', 150)->comment('Ej: Tour Europa 15 días');
            $table->text('descripcion')->nullable();
            $table->integer('duracion_dias')->unsigned();
            $table->decimal('precio_base', 10, 2);
            $table->timestamps();
            
            // Índices
            $table->index('destino_id');
            $table->index('precio_base');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_viajes');
    }
};
