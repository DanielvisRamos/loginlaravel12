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
        Schema::create('stands', function (Blueprint $table) {
            // Columnas básicas
            $table->id(); // ID único del stand
            $table->foreignId('event_id')
                  ->constrained('events')
                  ->onDelete('cascade'); // Relación 1:N con eventos
            $table->string('name'); // Nombre del stand
            $table->decimal('price', 10, 2); // Precio del stand (ej: 100.50)
            $table->enum('estado', ['disponible', 'reservado', 'ocupado', 'eliminado'])->default('disponible'); // Estado del stand
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stands');
    }
};
