<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preferencia_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->foreignId('tema_id')->nullable()->constrained('temas')->onDelete('set null');
            $table->enum('tamaño_fuente', ['pequeño', 'normal', 'grande', 'extra_grande'])->default('normal');
            $table->boolean('alto_contraste')->default(false);
            $table->boolean('modo_oscuro_auto')->default(true)->comment('Cambiar a modo oscuro según hora');
            $table->timestamps();
            
            // Índices
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preferencia_usuarios');
    }
};
