<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'surname',
        'CI',
        'email',
        'password',
        'address',
        'role_id',
        'estado',
    ];

    // Valores permitidos para el campo "estado"
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_INACTIVO = 'inactivo';
    const ESTADO_ELIMINADO = 'eliminado';

    // Relación: Un usuario pertenece a un rol
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // Sobrescribir el método "delete" para cambiar el estado en lugar de eliminar
    public function delete()
    {
        $this->estado = self::ESTADO_ELIMINADO;
        $this->save();
    }
    public function initials(): string
{
    $nombres = explode(' ', trim($this->name));
    $apellidos = explode(' ', trim($this->surname));

    $inicialNombre = $nombres[0][0] ?? '';
    $inicialApellido = $apellidos[0][0] ?? '';

    return strtoupper($inicialNombre . $inicialApellido);
}

}