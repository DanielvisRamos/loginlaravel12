<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Ejecuta las migraciones.
     * Este método es llamado cuando ejecutas los comandos 'php artisan migrate'.
     * Aquí definimos la estructura de las tablas 'users', 'password_reset_tokens' y 'sessions'.
     */
    public function up(): void
    {
        // Tabla de usuarios
        Schema::create('users', function (Blueprint $table) {
            // Columnas básicas
            $table->id(); // ID único del usuario (BIGINT UNSIGNED, autoincremental, clave primaria).
            $table->string('name', 50); // Nombre del usuario (VARCHAR de 50 caracteres).
            $table->string('surname', 50); // Apellido del usuario (VARCHAR de 50 caracteres).
            $table->string('CI', 15)->unique()->comment('Cédula de identidad única'); // Cédula de identidad única (VARCHAR de 15 caracteres), debe ser única, con un comentario descriptivo.
            $table->string('email')->unique(); // Correo electrónico único (VARCHAR).
            $table->string('password'); // Contraseña encriptada del usuario (VARCHAR).
            $table->string('address')->nullable(); // Dirección del usuario (VARCHAR), puede ser nulo.
            $table->foreignId('role_id')->constrained('roles')->default(2); // Clave foránea que referencia la tabla 'roles' (columna 'id').
                                                                         // 'constrained('roles')' asegura que exista una fila correspondiente en la tabla 'roles'.
                                                                         // 'default(2)' establece el valor predeterminado de 'role_id' a 2.
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active'); // Estado del usuario (ENUM con valores 'activo', 'inactivo', 'eliminado'), con 'activo' como valor predeterminado.
            $table->timestamp('email_verified_at')->nullable(); // Marca de tiempo para la verificación del correo electrónico, puede ser nulo hasta que se verifique.
            $table->rememberToken(); // Columna para almacenar el token de "Recordar sesión" (VARCHAR de 100 caracteres, puede ser nulo).
            $table->timestamps(); // Crea dos columnas: 'created_at' y 'updated_at' para registrar la fecha y hora de creación y modificación.
        });

        // Tabla para los tokens de restablecimiento de contraseña
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // Correo electrónico del usuario que solicitó el restablecimiento (VARCHAR), es la clave primaria.
            $table->string('token'); // Token único para el restablecimiento de la contraseña (VARCHAR).
            $table->timestamp('created_at')->nullable(); // Marca de tiempo de cuándo se generó el token, puede ser nulo.
        });

        // Tabla de sesiones de usuario
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // ID único de la sesión (VARCHAR), es la clave primaria.
            $table->foreignId('user_id')->nullable()->index(); // Clave foránea que referencia la tabla 'users' (columna 'id'), puede ser nulo si el usuario no está autenticado, indexada para mejorar las consultas.
            $table->string('ip_address', 45)->nullable(); // Dirección IP del usuario durante la sesión (VARCHAR de 45 caracteres), puede ser nulo.
            $table->text('user_agent')->nullable(); // Agente de usuario del navegador (TEXT), puede ser nulo.
            $table->longText('payload'); // Datos de la sesión serializados (LONGTEXT).
            $table->integer('last_activity')->index(); // Marca de tiempo de la última actividad del usuario (INTEGER), indexada para mejorar las consultas.
        });
    }

    /**
     * Revierte las migraciones.
     * Este método es llamado cuando ejecutas el comando 'php artisan migrate:rollback'.
     * Aquí definimos qué hacer para deshacer los cambios de la migración 'up()'.
     */
    public function down(): void
    {
        Schema::dropIfExists('users'); // Elimina la tabla 'users' de la base de datos.
        Schema::dropIfExists('password_reset_tokens'); // Elimina la tabla 'password_reset_tokens'.
        Schema::dropIfExists('sessions'); // Elimina la tabla 'sessions'.
    }
};