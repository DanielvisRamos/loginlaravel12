<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     * Este método es llamado cuando ejecutas los comandos 'php artisan migrate'.
     * Aquí definimos la estructura de la tabla 'reservations'.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            // Columnas básicas
            $table->id(); // ID único de la reserva (BIGINT UNSIGNED, autoincremental, clave primaria).

            // Clave foránea que referencia al stand reservado.
            $table->foreignId('stand_id') // Clave foránea que referencia al stand reservado.
                  ->constrained('stands') // Asegura que la 'stand_id' corresponda a un 'id' existente en la tabla 'stands'.
                  ->onDelete('cascade') // Si el stand es eliminado, también se eliminarán sus reservas asociadas.
                  ->index(); // Índice para optimizar las consultas que buscan reservas por stand.

            // Clave foránea que referencia al emprendimiento al que pertenece la reserva.
            // Se ha corregido el nombre de la tabla de referencia a 'entrepreneurships'.
            $table->foreignId('entrepreneurship_id') // Clave foránea que referencia al emprendimiento.
                  ->constrained('entrepreneurships', indexName: 'reservations_entrepreneurship_id_foreign') // Asegura que 'entrepreneurship_id' corresponda a un 'id' existente en la tabla 'entrepreneurships'. Nombre explícito para claridad y para prevenir conflictos.
                  ->onDelete('cascade'); // Si el emprendimiento es eliminado, también se eliminarán sus reservas asociadas.
            // Nota: foreignId()->constrained() ya añade un índice por defecto. Si se necesita un nombre explícito para el índice de la columna, se puede añadir:
            // $table->index('entrepreneurship_id', 'idx_reservations_entrepreneurship');

            // Clave foránea que referencia al usuario que realizó la reserva.
            // Esta columna se ha reintroducido para rastrear al usuario que creó la reserva.
            $table->foreignId('user_id') // Clave foránea que referencia al usuario que realizó la reserva.
                  ->constrained('users') // Asegura que la 'user_id' corresponda a un 'id' existente en la tabla 'users'.
                  ->onDelete('cascade'); // Si el usuario es eliminado, también se eliminarán sus reservas.


            // Fecha en la que se realizó la reserva (DATE), toma la fecha actual por defecto.
            $table->date('reservation_date')->useCurrent();

            // Estado de la reserva (ENUM con valores específicos).
            // 'pending_payment' ha sido añadido para reflejar el estado de pago pendiente.
            $table->enum('status', ['pending', 'confirmed', 'canceled', 'reserved', 'pending_payment'])->default('reserved');

            // Crea dos columnas: 'created_at' y 'updated_at' para registrar la fecha y hora de creación y modificación.
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     * Este método es llamado cuando ejecutas el comando 'php artisan migrate:rollback'.
     * Aquí definimos qué hacer para deshacer los cambios de la migración 'up()'.
     */
    public function down(): void
    {
        // Elimina la tabla 'reservations' de la base de datos.
        Schema::dropIfExists('reservations');
    }
};
