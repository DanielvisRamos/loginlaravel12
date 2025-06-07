<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Rutas de Administrador
|--------------------------------------------------------------------------
|
| Estas rutas son específicamente para el rol de 'admin'. Están agrupadas
| bajo el prefijo '/admin' y protegidas por los middlewares 'auth' y 'role:admin'
| para asegurar que solo los administradores autorizados puedan acceder a ellas.
|
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard del Administrador
    // Muestra el panel principal para los administradores.
    Route::view('dashboard', 'dashboard-admin') 
        ->name('dashboard');

    // Gestión de Emprendedores
    // 'admin.entrepreneurs.index' muestra una lista de todos los emprendedores.
    Volt::route('entrepreneurs', 'admin.entrepreneurs.index')
        ->name('entrepreneurs.index');

    // Gestión de Eventos
    // 'admin.events.index' muestra una lista de todos los eventos.
    Volt::route('events', 'admin.events.index')
        ->name('events.index');
    // 'admin.events.create' muestra el formulario para crear un nuevo evento.
    Volt::route('events/create', 'admin.events.create')
        ->name('events.create');

    // Gestión de Stands
    // 'admin.stands.index' muestra una lista de todos los stands.
    Volt::route('stands', 'admin.stands.index')
        ->name('stands.index');
    // 'admin.stands.manage' permite a los administradores gestionar los detalles del stand (ej. disponibilidad).
    Volt::route('stands/manage', 'admin.stands.manage')
        ->name('stands.manage');

    // Gestión de Emprendimientos
    // 'admin.entrepreneurships.index' muestra una lista de todos los emprendimientos para administración.
    Volt::route('entrepreneurships', 'admin.entrepreneurships.index')
        ->name('entrepreneurships.index');

    Volt::route('reservations', 'admin.reservations.index')
        ->name('reservations.index.admin');
});
