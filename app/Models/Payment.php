<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
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
        'reference_number',
        'paid_at',
        'status'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     * 'paid_at' se convierte a un objeto DateTime, y 'amount' se formatea a dos decimales.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    /**
     * Constantes para los posibles estados del pago.
     * Esto facilita el uso de estos valores en el código de una manera más legible y evita errores de escritura.
     */
    const STATUS_PENDING = 'pending';   // Pago pendiente.
    const STATUS_COMPLETED = 'completed'; // Pago completado.
    const STATUS_REFUNDED = 'refunded';  // Pago reembolsado.

    /**
     * Define la relación: Un pago pertenece a una reserva (relación uno a uno).
     * Esta relación se establece con el modelo 'Reservation' a través de la clave foránea 'reservation_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Método para marcar el pago como completado.
     * Actualiza el estado a 'completed' y registra la fecha y hora del pago.
     *
     * @return void
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now()
        ]);
    }

    /**
     * Método para marcar el pago como reembolsado.
     * Actualiza el estado a 'refunded'.
     *
     * @return void
     */
    public function markAsRefunded(): void
    {
        $this->update([
            'status' => self::STATUS_REFUNDED
        ]);
    }

    /**
     * Verifica si el pago está en estado 'pending'.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica si el pago está en estado 'completed'.
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verifica si el pago está en estado 'refunded'.
     *
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }
}