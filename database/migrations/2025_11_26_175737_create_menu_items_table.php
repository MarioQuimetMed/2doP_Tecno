<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
            $table->foreignId('rol_id')->nullable()->constrained('rols')->onDelete('set null');
            $table->string('titulo', 50);
            $table->string('ruta')->nullable();
            $table->string('icono', 50)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index('menu_id');
            $table->index('parent_id');
            $table->index('orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
