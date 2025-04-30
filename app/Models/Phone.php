<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Phone extends Model
{
    protected $fillable = [
        'user_id',
        'phone_number',
        'estado',
    ];

    // Estados permitidos
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_INACTIVO = 'inactivo';
    const ESTADO_ELIMINADO = 'eliminado';

    // Relación: Un teléfono pertenece a un usuario
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Eliminación lógica: en vez de borrar, cambia a 'eliminado'
    public function delete()
    {
        $this->estado = self::ESTADO_ELIMINADO;
        $this->save();
    }
}
// En este modelo, hemos definido los estados permitidos como constantes para facilitar su uso y evitar errores tipográficos.