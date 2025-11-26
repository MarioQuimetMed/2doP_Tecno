<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->string('descripcion')->nullable();
            $table->json('css_variables')->comment('Variables CSS del tema (colores, fuentes, etc.)');
            $table->enum('tipo', ['ninos', 'jovenes', 'adultos'])->comment('Tipo de tema');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temas');
    }
};
