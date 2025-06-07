<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entrepreneurship extends Model
{
    /**
     * Los atributos que se pueden asignar masivamente.
     * Estos campos pueden ser llenados al crear o actualizar un modelo usando métodos como `create` o `update`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'email',
        'social_networks',
        'registration_date',
        'logo_path',
        'status', // Se actualizó 'estado' a 'status' para consistencia con la base de datos.
        'user_id',
    ];

    /**
     * Constantes para los posibles valores del campo 'status'.
     * Esto facilita el uso de estos valores en el código de una manera más legible y evita errores de escritura.
     */
    const STATUS_ACTIVE = 'active';   // Emprendimiento activo.
    const STATUS_DELETED = 'deleted';  // Emprendimiento marcado como eliminado (soft delete personalizado).

    /**
     * Define la relación: Un emprendimiento pertenece a un usuario.
     * Esta relación se establece con el modelo 'User' a través de la clave foránea 'user_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sobrescribe el método "delete" del modelo.
     * En lugar de eliminar el registro de la base de datos, este método actualiza el campo 'status' a 'deleted'.
     * Esto implementa un tipo de "soft delete" personalizado.
     *
     * @return void
     */
    public function delete()
    {
        $this->status = self::STATUS_DELETED; // Establece el estado a 'deleted'.
        $this->save();                       // Guarda los cambios en la base de datos.
    }

    /**
     * Define un atributo accesor para obtener la URL del logo.
     * Si el 'logo_path' existe, retorna la URL completa utilizando la función 'asset' y 'storage'.
     * Si no existe, retorna una cadena vacía.
     *
     * @return string
     */
    public function getLogoUrlAttribute(): string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : '';
    }
}
// Este modelo representa un emprendimiento y define las relaciones y métodos necesarios para interactuar con la base de datos.