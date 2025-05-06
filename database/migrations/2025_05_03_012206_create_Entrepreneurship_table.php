<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntrepreneurshipTable extends Migration
{
    public function up(): void
    {
        Schema::create('emprendimientos', function (Blueprint $table) {
            $table->id('id_emprendedor'); // Primary Key
            $table->string('nombre_emprendimiento', 255);
            $table->text('descripcion_emprendimiento')->nullable();
            $table->string('correo', 255);
            $table->json('redes_sociales')->nullable(); // Para guardar Facebook, Instagram, TikTok
            $table->date('fecha_registro');

            // Foreign Keys
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_telefono');

            // Claves foráneas
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_telefono')->references('id')->on('phones')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emprendimientos');
    }
}