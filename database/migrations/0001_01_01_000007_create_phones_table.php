<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Ejecuta las migraciones.
     * Este método es llamado cuando ejecutas los comandos 'php artisan migrate'.
     * Aquí definimos la estructura de la tabla 'phones'.
     */
    public function up(): void
    {
        Schema::create('phones', function (Blueprint $table) {
            // Columnas básicas
            $table->id(); // ID único del teléfono (BIGINT UNSIGNED, autoincremental, clave primaria).
            $table->foreignId('user_id') // Clave foránea que referencia al usuario al que pertenece el teléfono.
                  ->constrained('users') // Asegura que la 'user_id' corresponda a un 'id' existente en la tabla 'users'.
                  ->onDelete('cascade'); // Si el usuario es eliminado, también se eliminarán sus números de teléfono asociados (relación uno a muchos).
            $table->string('phone_number'); // Número de teléfono (VARCHAR).
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active'); // Estado del teléfono (ENUM con valores 'active', 'inactive', 'deleted'), con 'active' como valor predeterminado.
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
        Schema::dropIfExists('phones'); // Elimina la tabla 'phones' de la base de datos.
    }
};