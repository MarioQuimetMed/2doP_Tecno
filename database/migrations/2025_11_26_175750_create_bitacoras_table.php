<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacoras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('accion', ['CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'VIEW', 'EXPORT'])->comment('Tipo de acción realizada');
            $table->string('tabla', 50)->nullable()->comment('Tabla afectada');
            $table->unsignedBigInteger('registro_id')->nullable()->comment('ID del registro afectado');
            $table->json('datos_anteriores')->nullable()->comment('Datos antes del cambio');
            $table->json('datos_nuevos')->nullable()->comment('Datos después del cambio');
            $table->string('ip', 45);
            $table->text('user_agent')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['user_id', 'created_at']);
            $table->index(['tabla', 'registro_id']);
            $table->index('accion');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacoras');
    }
};
