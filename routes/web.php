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
    volt::route('contador', 'contador')->name('contador');
    volt::route('emprendedores', 'emprendedores')->name('emprendedores');
    Volt::route('events', 'events.index')->name('events');
    Volt::route('events/admin', 'events.admin')->name('events.admin');
});


require __DIR__.'/auth.php';
