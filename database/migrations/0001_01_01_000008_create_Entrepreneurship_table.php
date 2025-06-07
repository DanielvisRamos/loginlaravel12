<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     * Este método crea la tabla 'entrepreneurships' en la base de datos.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('entrepreneurships', function (Blueprint $table) {
            $table->id(); // Columna de ID autoincremental (clave primaria)
            $table->string('name', 255); // Columna para el nombre del emprendimiento (cadena de hasta 255 caracteres)
            $table->text('description')->nullable(); // Columna para la descripción (texto largo, puede ser nulo)
            $table->string('email', 255); // Columna para el correo electrónico del emprendimiento (cadena de hasta 255 caracteres)
            $table->text('social_networks')->nullable(); // Columna para las redes sociales (texto, puede ser nulo)
            $table->date('registration_date'); // Columna para la fecha de registro (solo fecha)
            $table->string('logo_path')->nullable(); // Columna para la ruta del logo (cadena, puede ser nulo)

            // Columna para el estado del emprendimiento.
            // Se ha cambiado de 'estado' a 'status' para consistencia con el modelo.
            // Los valores permitidos son 'active' y 'deleted', con 'active' como valor por defecto.
            $table->enum('status', ['active', 'deleted'])->default('active');

            // Clave foránea que relaciona el emprendimiento con un usuario.
            // 'user_id' hace referencia a la columna 'id' de la tabla 'users'.
            // Si el usuario asociado es eliminado, el emprendimiento también se eliminará (onDelete('cascade')).
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->timestamps(); // Agrega las columnas 'created_at' y 'updated_at' automáticamente.
        });
    }

    /**
     * Revierte la migración.
     * Este método se ejecuta cuando se hace un 'migrate:rollback', eliminando la tabla 'entrepreneurships'.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('entrepreneurships'); // Elimina la tabla 'entrepreneurships' si existe.
    }
};