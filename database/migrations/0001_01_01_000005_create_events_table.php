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
        Schema::create('events', function (Blueprint $table) {
            // Columnas básicas
            $table->id(); // ID único del evento
            $table->string('name'); // Nombre del evento
            $table->text('description'); // Descripción detallada
            $table->string('address')->nullable();
            $table->dateTime('start_date'); // Fecha de inicio
            $table->dateTime('end_date'); // Fecha de fin
            $table->enum('estado', ['cursando','culminado', 'eliminado'])->default('cursando');
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('cascade'); // Usuario que creó el evento
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
