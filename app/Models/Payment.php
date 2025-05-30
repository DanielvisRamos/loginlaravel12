<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'reservation_id',
        'amount',
        'reference_number',
        'paid_at',
        'status'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    // Estados de pago
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REFUNDED = 'refunded';

    // Relación con la reserva (1:1)
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    // Marcar como completado
    public function markAsPaid(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now()
        ]);
    }

    // Marcar como reembolsado
    public function markAsRefunded(): void
    {
        $this->update([
            'status' => self::STATUS_REFUNDED
        ]);
    }

    // Verificaciones de estado
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }
}