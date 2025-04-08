<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $surname = '';
    public string $CI = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $address = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'CI' => ['required', 'string', 'min:8', 'max:20', 'unique:' . User::class], // Validación de CI
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class], // Validación de email
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'address' => ['nullable', 'string', 'max:255'], // Validación para la dirección (opcional)
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['estado'] = User::ESTADO_ACTIVO; // Asignar estado activo por defecto
        $validated['role_id'] = 2;

        // Crear el usuario
        event(new Registered(($user = User::create($validated))));

        // Iniciar sesión
        Auth::login($user);

        // Redirigir al dashboard
        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>
<div class="flex flex-col gap-6">
    <x-auth-header 
        :title="__('Crear una cuenta')" 
        :description="__('Ingresa tus datos a continuación para registrarte')" 
    />

    <!-- Estado de sesión -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Nombre -->
        <flux:input
            wire:model="name"
            :label="__('Nombre')"
            type="text"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Nombre completo')"
        />

        <!-- Apellido -->
        <flux:input
            wire:model="surname"
            :label="__('Apellido')"
            type="text"
            required
            autocomplete="surname"
            :placeholder="__('Apellido completo')"
        />

        <!-- CI -->
        <flux:input
            wire:model="CI"
            :label="__('CI')"
            type="text"
            required
            autocomplete="off"
            :placeholder="__('Ingresa tu CI (ej: 12345678)')"
        />
        @error('CI') 
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Correo electrónico -->
        <flux:input
            wire:model="email"
            :label="__('Correo electrónico')"
            type="email"
            required
            autocomplete="email"
            placeholder="correo@ejemplo.com"
        />
        @error('email')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Contraseña -->
        <flux:input
            wire:model="password"
            :label="__('Contraseña')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Contraseña')"
        />
        @error('password') 
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Confirmar contraseña -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirmar contraseña')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirmar contraseña')"
        />
        @error('password_confirmation') 
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Dirección -->
        <flux:input
            wire:model="address"
            :label="__('Dirección')"
            type="text"
            optional
            autocomplete="address"
            :placeholder="__('Tu dirección (opcional)')"
        />
        @error('address') 
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Botón para crear cuenta -->
        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Crear cuenta') }}
            </flux:button>
        </div>
    </form>

    <!-- Ya tienes cuenta -->
    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('¿Ya tienes una cuenta?') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('Inicia sesión') }}</flux:link>
    </div>
</div>
