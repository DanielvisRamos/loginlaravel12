<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
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
        'address',
        'start_date',
        'end_date',
        'status', // Se actualizó 'estado' a 'status' para consistencia con la base de datos.
        'created_by',
    ];

    /**
     * Constantes para los posibles valores del campo 'status'.
     * Esto facilita el uso de estos valores en el código de una manera más legible y evita errores de escritura.
     */
    const STATUS_ONGOING = 'ongoing';   // Evento en curso.
    const STATUS_COMPLETED = 'completed'; // Evento culminado.
    const STATUS_DELETED = 'deleted';    // Evento marcado como eliminado (soft delete personalizado).

    /**
     * Define la relación: Un evento pertenece al usuario que lo creó.
     * Esta relación se establece con el modelo 'User' a través de la clave foránea 'created_by'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Define la relación: Un evento puede tener muchos stands.
     * Esta relación se establece con el modelo 'Stand'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stands(): HasMany
    {
        return $this->hasMany(Stand::class);
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