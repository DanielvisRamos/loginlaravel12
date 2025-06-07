<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     * Este método es llamado cuando ejecutas los comandos 'php artisan migrate'.
     * Aquí definimos la estructura de la tabla 'events'.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            // Columnas básicas
            $table->id(); // ID único del evento (BIGINT UNSIGNED, autoincremental, clave primaria).
            $table->string('name'); // Nombre del evento (VARCHAR).
            $table->text('description'); // Descripción detallada del evento (TEXT).
            $table->string('address')->nullable(); // Dirección del evento (VARCHAR), puede ser nulo.
            $table->dateTime('start_date'); // Fecha y hora de inicio del evento (DATETIME).
            $table->dateTime('end_date'); // Fecha y hora de finalización del evento (DATETIME).
            $table->enum('status', ['ongoing', 'completed', 'deleted'])->default('ongoing'); // Estado del evento (ENUM con valores 'ongoing', 'completed', 'deleted'), con 'ongoing' como valor predeterminado.
            $table->foreignId('created_by') // Clave foránea que referencia al usuario que creó el evento.
                  ->constrained('users') // Asegura que la 'created_by' corresponda a un 'id' existente en la tabla 'users'.
                  ->onDelete('cascade'); // Si el usuario creador es eliminado, también se eliminarán sus eventos asociados.
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
        Schema::dropIfExists('events'); // Elimina la tabla 'events' de la base de datos.
    }
};