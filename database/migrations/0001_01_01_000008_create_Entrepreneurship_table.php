<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('entrepreneurships', function (Blueprint $table) {
            $table->id(); // ID principal del emprendimiento
            $table->string('name', 255); // Nombre del emprendimiento
            $table->text('description')->nullable(); // Descripción opcional
            $table->string('email', 255); // Correo electrónico del emprendimiento
            $table->json('social_networks')->nullable(); // Redes sociales en formato JSON
            $table->date('registration_date'); // Fecha de registro del emprendimiento
            $table->string('logo_path')->nullable(); // Ruta del logo del emprendimiento

            // Estado: 'activo' o 'eliminado', por defecto 'activo'
            $table->enum('estado', ['activo', 'eliminado'])->default('activo'); 

            // Claves foráneas
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relación con usuarios

            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrepreneurships');
    }
};
