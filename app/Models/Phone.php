<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Phone extends Model
{
    /**
     * Los atributos que se pueden asignar masivamente.
     * Estos campos pueden ser llenados al crear o actualizar un modelo usando métodos como `create` o `update`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'phone_number',
        'status', // Se actualizó 'estado' a 'status' para consistencia con la base de datos.
    ];

    /**
     * Constantes para los posibles valores del campo 'status'.
     * Esto facilita el uso de estos valores en el código de una manera más legible y evita errores de escritura.
     */
    const STATUS_ACTIVE = 'active';   // Teléfono activo.
    const STATUS_INACTIVE = 'inactive'; // Teléfono inactivo.
    const STATUS_DELETED = 'deleted';  // Teléfono marcado como eliminado (soft delete personalizado).

    /**
     * Define la relación: Un teléfono pertenece a un usuario.
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
}