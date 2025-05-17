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
        Schema::create('reservations', function (Blueprint $table) {
            // Columnas básicas
            $table->id(); // ID único de la reserva
            $table->foreignId('stand_id')
                  ->constrained('stands')
                  ->onDelete('cascade')->index(); // Relación 1:N con stands
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // Relación 1:N con usuarios
            $table->date('reservation_date')->useCurrent(); // Fecha de la reserva
            $table->enum('status', ['pending', 'confirmed', 'canceled'])->default('pending'); // Estado de la reserva
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
