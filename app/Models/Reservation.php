<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    protected $fillable = [
        'stand_id',
        'user_id',
        'reservation_date',
        'status'
    ];

    // Estados de la reserva (exactamente como en tu migración)
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELED = 'canceled';

    // Relación con el stand (1:N)
    public function stand(): BelongsTo
    {
        return $this->belongsTo(Stand::class);
    }

    // Relación con el usuario (1:N)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el pago (1:1 - aunque no está en la migración, la mencionaste como requerimiento)
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    // Métodos para gestión de estados
    public function confirm(): void
    {
        $this->update(['status' => self::STATUS_CONFIRMED]);
    }

    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELED]);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }
}