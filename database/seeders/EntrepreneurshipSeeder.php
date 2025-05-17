<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entrepreneurship;
use App\Models\User;
use Illuminate\Support\Str;

class EntrepreneurshipSeeder extends Seeder
{
    public function run(): void
    {
        $emprendedores = User::where('role_id', 2)->get();

        foreach ($emprendedores as $user) {
            Entrepreneurship::create([
                'name' => 'Emprendimiento de ' . $user->name,
                'description' => 'Descripción del emprendimiento de ' . $user->name . '.',
                'email' => Str::slug($user->name) . '.emprendimiento@example.com',
                'social_networks' => json_encode(['Facebook', 'Instagram']), // ✅ codificado como JSON
                'registration_date' => now(),
                'logo_path' => 'logos/' . Str::slug($user->name) . '_emprendimiento.png',
                'estado' => Entrepreneurship::ESTADO_ACTIVO,
                'user_id' => $user->id,
            ]);
        }
    }
}
