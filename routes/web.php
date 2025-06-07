<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Rutas Web
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas web para tu aplicación. Estas
| rutas son cargadas por el RouteServiceProvider y todas ellas serán
| asignadas al grupo de middleware "web". ¡Haz algo genial!
|
*/

// Ruta de la página de inicio
// Esta ruta muestra la página de bienvenida cuando un usuario accede a la URL raíz.
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rutas de configuración comunes para cualquier usuario autenticado
// Estas rutas son accesibles por cualquier usuario autenticado para gestionar su perfil,
// contraseña, apariencia y números de teléfono.
Route::middleware(['auth'])->group(function () {
    // Redirige /settings a /settings/profile para una vista predeterminada de configuración.
    Route::redirect('settings', 'settings/profile');

    // Rutas Volt para la configuración del usuario.
    // 'settings.profile' muestra la configuración del perfil del usuario.
    Volt::route('settings/profile', 'settings.profile')
        ->name('settings.profile');
    // 'settings.password' permite al usuario cambiar su contraseña.
    Volt::route('settings/password', 'settings.password')
        ->name('settings.password');
    // 'settings.appearance' permite al usuario personalizar la apariencia de la interfaz de usuario.
    Volt::route('settings/appearance', 'settings.appearance')
        ->name('settings.appearance');
    // 'settings.phone-manager' permite al usuario gestionar sus números de teléfono.
    Volt::route('settings/phone', 'settings.phone-manager')
        ->name('settings.phone');
});

// Importa las rutas de autenticación desde 'auth.php'.
// Este archivo típicamente maneja el inicio de sesión, registro, restablecimiento de contraseña, etc.
require __DIR__ . '/auth.php';

// Importa las rutas específicas del administrador desde 'admin.php'.
// Estas rutas están protegidas y solo son accesibles por usuarios con el rol de 'admin'.
require __DIR__ . '/admin.php';

// Importa las rutas específicas del emprendedor desde 'entrepreneur.php'.
// Estas rutas están protegidas y solo son accesibles por usuarios con el rol de 'emprendedor'.
require __DIR__ . '/entrepreneur.php';
