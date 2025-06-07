<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     * Este método es llamado cuando ejecutas los comandos 'php artisan migrate'.
     * Aquí definimos la estructura de la tabla 'refunds'.
     */
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id(); // ID único del reembolso (BIGINT UNSIGNED, autoincremental, clave primaria).
            $table->foreignId('reservation_id') // Clave foránea que referencia a la reserva asociada al reembolso.
                  ->constrained( // Define la restricción de clave foránea.
                      table: 'reservations',
                      indexName: 'refunds_reservation_id_foreign' // Nombre explícito para el índice de la clave foránea.
                  )
                  ->unique(); // Asegura que cada reserva tenga un único reembolso asociado (relación uno a uno).
            $table->decimal('amount', 10, 2); // Monto del reembolso (DECIMAL con 10 dígitos en total, 2 de ellos decimales).
            $table->string('reason', 500); // Razón del reembolso (VARCHAR de 500 caracteres).
            $table->dateTime('processed_at')->nullable(); // Fecha y hora en que se procesó el reembolso (DATETIME), puede ser nulo hasta que se complete.
            $table->enum('status', ['pending', 'completed'])->default('pending'); // Estado del reembolso (ENUM con valores 'pending', 'completed'), con 'pending' como valor predeterminado.
            $table->timestamps(); // Crea dos columnas: 'created_at' y 'updated_at' para registrar la fecha y hora de creación y modificación.
        });
    }

    /**
     * Revierte las migraciones.
     * Este método es llamado cuando ejecutas el comando 'php artisan migrate:rollback'.
     * Aquí definimos qué hacer para deshacer los cambios de la migración 'up()'.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds'); // Elimina la tabla 'refunds' de la base de datos.
    }
};