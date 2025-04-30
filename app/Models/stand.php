<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stand extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'price',
        'estado',
    ];

    const ESTADO_DISPONIBLE = 'disponible';
    const ESTADO_RESERVADO = 'reservado';
    const ESTADO_OCUPADO = 'ocupado';
    const ESTADO_ELIMINADO = 'eliminado';

    // Relación con el evento
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Eliminación lógica
    public function delete()
    {
        $this->estado = self::ESTADO_ELIMINADO;
        $this->save();
    }
}
// Este modelo representa un stand en un evento. Contiene información básica como el ID del evento, el nombre del stand, su precio y su estado (disponible, reservado, ocupado o eliminado). También incluye una relación con el modelo Event y un método para realizar la eliminación lógica del stand.