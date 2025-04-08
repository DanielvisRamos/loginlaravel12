<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Danielvis',
            'surname' => 'Ramos',
            'CI' => '31014461',
            'email' => 'danielvisramos31@gmail.com',
            'password' => bcrypt('Admin'),
            'role_id' => 1, // ID del rol "admin"
            'estado' => User::ESTADO_ACTIVO,
        ]);
    }
}