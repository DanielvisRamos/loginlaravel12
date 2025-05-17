<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entrepreneurship extends Model
{
    protected $fillable = [
        'name',
        'description',
        'email',
        'social_networks',
        'registration_date',
        'logo_path',
        'estado',
        'user_id',
    ];

    // Estados permitidos
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_ELIMINADO = 'eliminado';

    /**
     * Relación con el usuario
     * Un emprendimiento pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Eliminación lógica
     * Cambia el estado a 'eliminado'
     */
    public function delete()
    {
        $this->estado = self::ESTADO_ELIMINADO;
        $this->save();
    }

    /**
     * Obtiene la URL del logo
     * Retorna la URL del logo si existe, sino retorna una cadena vacía
     */
    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : '';
    }
}
// Este modelo representa un emprendimiento y define las relaciones y métodos necesarios para interactuar con la base de datos.