<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'reservation_id',
        'amount',
        'reason',
        'processed_at',
        'status'
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    // Estados del reembolso
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';

    // Relación con la reserva (1:1)
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    // Marcar como completado
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now()
        ]);
    }

    // Verificaciones de estado
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}