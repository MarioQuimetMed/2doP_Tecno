<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visita_paginas', function (Blueprint $table) {
            $table->id();
            $table->string('ruta');
            $table->string('ip', 45);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Índices para optimizar consultas de conteo
            $table->index(['ruta', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visita_paginas');
    }
};
