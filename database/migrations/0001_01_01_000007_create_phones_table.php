<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('phones', function (Blueprint $table) {
            // Columnas básicas
            $table->id(); // ID único del teléfono
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Relación 1:N con usuarios (si se elimina un usuario, se borran sus teléfonos)
            $table->string('phone_number'); // Número de teléfono
            $table->enum('estado', ['activo', 'inactivo', 'eliminado'])->default('activo'); // Estado del teléfono (activo, inactivo, eliminado)
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phones');
    }
};
