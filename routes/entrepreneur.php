<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Rutas para Emprendedores
|--------------------------------------------------------------------------
|
| Estas rutas son específicamente para el rol de 'emprendedor'. Están agrupadas
| bajo el prefijo '/entrepreneur' y protegidas por los middlewares 'auth' y 'role:emprendedor'
| para asegurar que solo los emprendedores autorizados puedan acceder a ellas.
|
*/

Route::middleware(['auth', 'role:emprendedor'])->prefix('entrepreneur')->name('entrepreneur.')->group(function () {
    // Dashboard del Emprendedor
    // Muestra el panel principal para los emprendedores.
    Route::view('dashboard', 'dashboard-entrepreneur') // Asumiendo que 'dashboard-entrepreneur' es la vista Blade para el dashboard del emprendedor.
        ->name('dashboard');

    // Gestión del Emprendimiento Propio del Usuario
    // 'entrepreneur.entrepreneurships.show' muestra los detalles del emprendimiento propio del emprendedor autenticado.
    // Esto asume que un emprendedor gestiona un único emprendimiento principal.
    Volt::route('my-entrepreneurship', 'entrepreneur.entrepreneurships.show')
        ->name('entrepreneurships.show');
    Volt::route('my-reservations', 'entrepreneur.reservations.index')
        ->name('reservations.index');
});
