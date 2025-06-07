<?php

namespace Database\Seeders;

use App\Models\User; // Importamos el modelo User
use Illuminate\Database\Seeder; // Importamos la clase Seeder

class UserSeeder extends Seeder
{
    /**
     * Ejecuta las operaciones de siembra de la base de datos.
     * Este método se encarga de crear registros iniciales para el modelo User.
     *
     * @return void
     */
    public function run(): void
    {
        // Creamos un nuevo usuario con los datos especificados.
        User::create([
            'name' => 'Danielvis', // Nombre del usuario
            'surname' => 'Ramos', // Apellido del usuario
            'CI' => 'V-31014461', // Cédula de Identidad, adaptada para incluir el prefijo 'V-'
            'email' => 'danielvisramos31@gmail.com', // Correo electrónico del usuario
            // Contraseña encriptada. Se ha actualizado para cumplir con las nuevas reglas de robustez
            // (mínimo 8 caracteres, mayúsculas, minúsculas, números y símbolos).
            'password' => bcrypt('Admin@123'), // Ejemplo de contraseña robusta
            'role_id' => 1, // ID del rol para este usuario (asumiendo que '1' es el rol de administrador)
            'status' => User::STATUS_ACTIVE, // Estado del usuario, usando la constante STATUS_ACTIVE
        ]);

        // Puedes añadir más usuarios si es necesario, siguiendo el mismo patrón.
        // Ejemplo de otro usuario (emprendedor):
        /*
        User::create([
            'name' => 'Emprendedor',
            'surname' => 'Ejemplo',
            'CI' => 'E-12345678',
            'email' => 'emprendedor@example.com',
            'password' => bcrypt('Emprendedor@123'),
            'role_id' => 2, // Asumiendo que '2' es el ID del rol "emprendedor"
            'status' => User::STATUS_ACTIVE,
        ]);
        */
    }
}