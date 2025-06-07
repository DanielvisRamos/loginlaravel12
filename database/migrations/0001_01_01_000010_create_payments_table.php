<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     * Este método es llamado cuando ejecutas los comandos 'php artisan migrate'.
     * Aquí definimos la estructura de la tabla 'payments'.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // ID único del pago (BIGINT UNSIGNED, autoincremental, clave primaria).
            $table->foreignId('reservation_id') // Clave foránea que referencia a la reserva asociada al pago.
                  ->constrained( // Define la restricción de clave foránea.
                      table: 'reservations',
                      indexName: 'payments_reservation_id_foreign' // Nombre explícito para el índice de la clave foránea.
                  )
                  ->unique(); // Asegura que cada reserva tenga un único pago asociado (relación uno a uno).
            $table->decimal('amount', 10, 2); // Monto del pago (DECIMAL con 10 dígitos en total, 2 de ellos decimales).
            $table->string('reference_number')->unique(); // Número de referencia del pago (VARCHAR), debe ser único.
            $table->dateTime('paid_at')->nullable(); // Fecha y hora en que se realizó el pago (DATETIME), puede ser nulo hasta que se complete el pago.
            $table->enum('status', ['pending', 'completed', 'refunded'])->default('pending'); // Estado del pago (ENUM con valores 'pending', 'completed', 'refunded'), con 'pending' como valor predeterminado.
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
        Schema::dropIfExists('payments'); // Elimina la tabla 'payments' de la base de datos.
    }
};