<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stand extends Model
{
    /**
     * Los atributos que se pueden asignar masivamente.
     * Estos campos pueden ser llenados al crear o actualizar un modelo usando métodos como `create` o `update`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'name',
        'price',
        'status', // Se actualizó 'estado' a 'status' para consistencia con la base de datos.
    ];

    /**
     * Constantes para los posibles valores del campo 'status'.
     * Esto facilita el uso de estos valores en el código de una manera más legible y evita errores de escritura.
     */
    const STATUS_AVAILABLE = 'available'; // Stand disponible.
    const STATUS_RESERVED = 'reserved';   // Stand reservado.
    const STATUS_OCCUPIED = 'occupied';   // Stand ocupado.
    const STATUS_DELETED = 'deleted';    // Stand marcado como eliminado (soft delete personalizado).

    /**
     * Define la relación: Un stand pertenece a un evento.
     * Esta relación se establece con el modelo 'Event' a través de la clave foránea 'event_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Define la relación: Un stand puede tener muchas reservas.
     * Esta relación se establece con el modelo 'Reservation'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
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