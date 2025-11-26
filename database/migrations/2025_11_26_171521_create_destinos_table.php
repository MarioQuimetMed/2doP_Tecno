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
        Schema::create('destinos', function (Blueprint $table) {
            $table->id();
            $table->string('pais', 100);
            $table->string('ciudad', 100);
            $table->string('nombre_lugar', 150)->comment('Ej: Machu Picchu, Salar de Uyuni');
            $table->text('descripcion')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['pais', 'ciudad']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinos');
    }
};
