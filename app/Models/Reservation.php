<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany; // Asegúrate de importar HasMany

class Reservation extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     * Estos campos pueden ser llenados al crear o actualizar un modelo usando métodos como `create` o `update`.
     * Se ha añadido 'user_id' para reflejar la migración actualizada.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'stand_id',
        'entrepreneurship_id',
        'user_id', // Añadido para que coincida con la migración
        'reservation_date',
        'status',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     * Principalmente para las fechas y horas.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reservation_date' => 'date',
    ];

    /**
     * Constantes para los posibles estados de la reserva.
     * Esto facilita el uso de estos valores en el código de una manera más legible y evita errores de escritura.
     * Ahora 'pending' abarca tanto la revisión general como el pago pendiente.
     */
    public const STATUS_PENDING = 'pending'; // Reserva pendiente (estado general, incluyendo pago pendiente).
    public const STATUS_CONFIRMED = 'confirmed'; // Reserva confirmada.
    public const STATUS_CANCELED = 'canceled'; // Reserva cancelada.
    public const STATUS_RESERVED = 'reserved'; // Reserva en estado inicial (reservada pero no pagada/confirmada).


    /**
     * Define la relación: Una reserva pertenece a un stand.
     * Esta relación se establece con el modelo 'Stand' a través de la clave foránea 'stand_id'.
     *
     * @return BelongsTo
     */
    public function stand(): BelongsTo
    {
        return $this->belongsTo(Stand::class);
    }

    /**
     * Define la relación: Una reserva pertenece a un emprendimiento.
     * Esta relación se establece con el modelo 'Entrepreneurship' a través de la clave foránea 'entrepreneurship_id'.
     *
     * @return BelongsTo
     */
    public function entrepreneurship(): BelongsTo
    {
        return $this->belongsTo(Entrepreneurship::class, 'entrepreneurship_id');
    }

    /**
     * Define la relación: Una reserva fue realizada por un usuario.
     * Esta relación se establece con el modelo 'User' a través de la clave foránea 'user_id'.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define la relación: Una reserva tiene un pago asociado (relación uno a uno).
     * Esta relación se establece con el modelo 'Payment'.
     *
     * @return HasOne
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Define la relación: Una reserva puede tener una solicitud de reembolso (uno a uno).
     * Esto es útil si solo esperas una solicitud de reembolso activa por reserva.
     *
     * @return HasOne
     */
    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class);
    }

    /**
     * Define la relación: Una reserva puede tener múltiples solicitudes de reembolso (uno a muchos).
     * Esto es útil si necesitas rastrear el historial de solicitudes de reembolso.
     *
     * @return HasMany
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Método para confirmar la reserva.
     * Actualiza el estado de la reserva a 'confirmed'.
     *
     * @return void
     */
    public function confirm(): void
    {
        $this->update(['status' => self::STATUS_CONFIRMED]);
    }

    /**
     * Método para cancelar la reserva.
     * Actualiza el estado de la reserva a 'canceled'.
     *
     * @return void
     */
    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELED]);
    }

    /**
     * Verifica si la reserva está en estado 'pending'.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica si la reserva está en estado 'confirmed'.
     *
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Verifica si la reserva está en estado 'canceled'.
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * Verifica si la reserva está en estado 'reservado'.
     *
     * @return bool
     */
    public function isReserved(): bool
    {
        return $this->status === self::STATUS_RESERVED;
    }

    /**
     * Verifica si la reserva está en estado 'pending_payment'.
     * Ahora, este método verifica si el estado es 'pending', asumiendo
     * que 'pending' cubre el estado de pago pendiente.
     *
     * @return bool
     */
    public function isPendingPayment(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
