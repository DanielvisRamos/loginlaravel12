<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'name',
        'description',
        'address',
        'start_date',
        'end_date',
        'estado',
        'created_by',
    ];
    const ESTADO_CURSANDO = 'cursando';
    const ESTADO_CULMINADO = 'culminado';
    const ESTADO_ELIMINADO = 'eliminado';

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function delete()
    {
        $this->estado = self::ESTADO_ELIMINADO;
        $this->save();
    }

}

