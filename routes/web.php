<?php

use App\Http\Livewire\Emprendedores;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');


// Dashboard para admin (protegido por rol)
Route::view('dashboard', 'dashboardAdmin')
    ->middleware(['auth', 'verified', 'role:admin'])
    ->name('dashboard');

// Dashboard para emprendedor (protegido por rol)
Route::view('dashboard-emprendedor', 'dashboardEntrepreneur')
    ->middleware(['auth', 'verified', 'role:emprendedor'])
    ->name('dashboard.emprendedor');

// Configuración común para cualquier usuario autenticado
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('settings/phone', 'settings.phoneManager')->name('settings.phoneManager');
});

// Rutas exclusivas para ADMIN
Route::middleware(['auth', 'role:admin'])->group(function () {
    Volt::route('emprendedores', 'admin.Entrepreneurs.index')->name('emprendedores');
    Volt::route('events', 'admin.events.index')->name('events');
    Volt::route('events/admin', 'admin.events.register2')->name('events.register');
    Volt::route('stands', 'admin.stands.index')->name('stands');
    Volt::route('stands/admin', 'admin.stands.admin')->name('stands.admin');
});

require __DIR__ . '/auth.php';
