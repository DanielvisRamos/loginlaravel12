<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Asegúrate de que HasMany esté importado

class User extends Authenticatable
{
    /**
     * Los atributos que se pueden asignar masivamente.
     * Estos campos pueden ser llenados al crear o actualizar un modelo usando métodos como `create` o `update`.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'CI',
        'email',
        'password',
        'address',
        'role_id',
        'status', // Se actualizó 'estado' a 'status' para consistencia con la base de datos.
    ];

    /**
     * Constantes para los posibles valores del campo 'status'.
     * Esto facilita el uso de estos valores en el código de una manera más legible y evita errores de escritura.
     */
    const STATUS_ACTIVE = 'active';    // Usuario activo.
    const STATUS_INACTIVE = 'inactive'; // Usuario inactivo.
    const STATUS_DELETED = 'deleted';  // Usuario marcado como eliminado (soft delete personalizado).

    /**
     * Define la relación: Un usuario pertenece a un rol.
     * Esta relación se establece con el modelo 'Role' a través de la clave foránea 'role_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Define la relación: Un usuario tiene muchos teléfonos.
     * Esta relación se establece con el modelo 'Phone'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }

    /**
     * Define la relación: Un usuario puede tener múltiples emprendimientos.
     * Esta relación se establece con el modelo 'Entrepreneurship'.
     * Asume que la tabla 'entrepreneurships' tiene una columna 'user_id'
     * que referencia al 'id' del usuario en la tabla 'users'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entrepreneurships(): HasMany
    {
        return $this->hasMany(Entrepreneurship::class);
    }

    /**
     * Sobrescribe el método "delete" del modelo.
     * En lugar de eliminar el registro de la base de datos, este método actualiza el campo 'status' a 'deleted'.
     * Esto implementa un tipo de "soft delete" personalizado.
     *
     * @return void
     */
    public function delete()
    {
        $this->status = self::STATUS_DELETED; // Establece el estado a 'deleted'.
        $this->save();                       // Guarda los cambios en la base de datos.
    }

    /**
     * Genera las iniciales del usuario a partir de su nombre y apellido.
     * Toma la primera letra del primer nombre y la primera letra del primer apellido, y las devuelve en mayúsculas.
     *
     * @return string
     */
    public function initials(): string
    {
        $nombres = explode(' ', trim($this->name));    // Divide el nombre en palabras.
        $apellidos = explode(' ', trim($this->surname)); // Divide el apellido en palabras.

        $inicialNombre = $nombres[0][0] ?? '';    // Obtiene la primera letra del primer nombre (o vacío si no hay).
        $inicialApellido = $apellidos[0][0] ?? ''; // Obtiene la primera letra del primer apellido (o vacío si no hay).

        return strtoupper($inicialNombre . $inicialApellido); // Concatena las iniciales y las convierte a mayúsculas.
    }
}
