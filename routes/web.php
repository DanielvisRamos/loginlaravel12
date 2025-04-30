<?php

use App\Http\Livewire\Emprendedores;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('settings/phone', 'settings.phoneManager')->name('settings.phoneManager');
    volt::route('emprendedores', 'Entrepreneurs.index')->name('emprendedores');
    Volt::route('events', 'events.index')->name('events');
    Volt::route('events/admin', 'events.register2')->name('events.register');
    Volt::route('stands', 'stands.index')->name('stands');
    Volt::route('stands/admin', 'stands.admin')->name('stands.admin');
});


require __DIR__.'/auth.php';
