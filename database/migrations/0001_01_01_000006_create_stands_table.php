<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     * Este método es llamado cuando ejecutas los comandos 'php artisan migrate'.
     * Aquí definimos la estructura de la tabla 'stands'.
     */
    public function up(): void
    {
        Schema::create('stands', function (Blueprint $table) {
            // Columnas básicas
            $table->id(); // ID único del stand (BIGINT UNSIGNED, autoincremental, clave primaria).
            $table->foreignId('event_id') // Clave foránea que referencia al evento al que pertenece el stand.
                  ->constrained('events') // Asegura que la 'event_id' corresponda a un 'id' existente en la tabla 'events'.
                  ->onDelete('cascade'); // Si el evento es eliminado, también se eliminarán sus stands asociados (relación uno a muchos).
            $table->string('name'); // Nombre del stand (VARCHAR).
            $table->decimal('price', 10, 2); // Precio del stand (DECIMAL con 10 dígitos en total, 2 de ellos decimales).
            $table->enum('status', ['available', 'reserved', 'occupied', 'deleted'])->default('available'); // Estado del stand (ENUM con valores 'available', 'reserved', 'occupied', 'deleted'), con 'available' como valor predeterminado.
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
        Schema::dropIfExists('stands'); // Elimina la tabla 'stands' de la base de datos.
    }
};