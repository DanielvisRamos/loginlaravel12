<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    /**
     * Los atributos que se pueden asignar masivamente.
     * Estos campos pueden ser llenados al crear o actualizar un modelo usando métodos como `create` o `update`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservation_id',
        'amount',
        'reason',
        'processed_at',
        'status'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     * 'processed_at' se convierte a un objeto DateTime, y 'amount' se formatea a dos decimales.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'processed_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    /**
     * Constantes para los posibles estados del reembolso.
     * Esto facilita el uso de estos valores en el código de una manera más legible y evita errores de escritura.
     */
    const STATUS_PENDING = 'pending';   // Reembolso pendiente.
    const STATUS_COMPLETED = 'completed'; // Reembolso completado.

    /**
     * Define la relación: Un reembolso pertenece a una reserva (relación uno a uno).
     * Esta relación se establece con el modelo 'Reservation' a través de la clave foránea 'reservation_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Método para marcar el reembolso como completado.
     * Actualiza el estado a 'completed' y registra la fecha y hora en que se procesó.
     *
     * @return void
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now()
        ]);
    }

    /**
     * Verifica si el reembolso está en estado 'pending'.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica si el reembolso está en estado 'completed'.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}