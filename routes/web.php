<?php

use App\Http\Livewire\Emprendedores;
use App\Models\Entrepreneurship;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard para administrador (protegido por rol)
Route::view('dashboard', 'dashboardAdmin')
    ->middleware(['auth', 'verified', 'role:admin'])
    ->name('dashboard');

// Dashboard para emprendedor (protegido por rol)
Route::view('dashboard-entrepreneur', 'dashboardEntrepreneur')
    ->middleware(['auth', 'verified', 'role:emprendedor'])
    ->name('dashboard.entrepreneur');

// Configuración común para cualquier usuario autenticado
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')
        ->name('settings.profile');
    Volt::route('settings/password', 'settings.password')
        ->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')
        ->name('settings.appearance');
    Volt::route('settings/phone', 'settings.phoneManager')
        ->name('settings.phone');
});

// Rutas exclusivas para ADMIN
Route::middleware(['auth', 'role:admin'])->group(function () {
    Volt::route('entrepreneurs', 'admin.entrepreneurs.index')
        ->name('entrepreneurs');
    Volt::route('events', 'admin.events.index')
        ->name('events');
    Volt::route('events/register', 'admin.events.register')
        ->name('events.register');
    Volt::route('stands', 'admin.stands.index')
        ->name('stands');
    Volt::route('stands/admin', 'admin.stands.admin')
        ->name('stands.admin');
    volt::route('Entrepreneurship' , 'admin.entrepreneurship.index')->name('entrepreneurship');
});

require __DIR__ . '/auth.php';