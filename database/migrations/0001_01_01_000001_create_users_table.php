<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabla users (Ajustada a estándar Laravel + rol)
        Schema::create('users', function (Blueprint $table) {
            // Columnas básicas
            $table->id(); // ID único del usuario
            $table->string('name',50); // Nombre del usuario
            $table->string('surname',50); // Nombre del usuario
            $table->string('CI',15)->unique()->comment('Cédula de identidad única');
            $table->string('email')->unique(); // Correo electrónico único
            $table->string('password'); // Contraseña encriptada
            $table->string('address')->nullable();
            $table->foreignId('role_id')->constrained('roles')->default(2);
            $table->enum('estado', ['activo', 'inactivo', 'eliminado'])->default('activo');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken(); // Token para "Recordar sesión"
            $table->timestamps(); // created_at y updated_at
        });

        // Tabla password_reset_tokens (Estándar Laravel)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // Campo estándar
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Tabla sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
