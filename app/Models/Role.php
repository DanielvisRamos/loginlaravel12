<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name'];

    // Relación: Un rol puede tener muchos usuarios
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}