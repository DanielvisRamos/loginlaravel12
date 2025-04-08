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
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <flux:input
            wire:model="name"
            :label="__('Name')"
            type="text"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Full name')"
        />

        <!-- Surname -->
        <flux:input
            wire:model="surname"
            :label="__('Surname')"
            type="text"
            required
            autocomplete="surname"
            :placeholder="__('Full surname')"
        />

        <!-- CI -->
        <flux:input
            wire:model="CI"
            :label="__('CI')"
            type="text"
            required
            autocomplete="off"
            :placeholder="__('Enter your CI (e.g., 12345678)')"
        />
        @error('CI') 
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autocomplete="email"
            placeholder="email@example.com"
        />
        @error('email')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
        />
        @error('password') 
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirm password')"
        />
        @error('password_confirmation') 
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Address -->
        <flux:input
            wire:model="address"
            :label="__('Address')"
            type="text"
            optional
            autocomplete="address"
            :placeholder="__('Your address (optional)')"
        />
        @error('address') 
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Already have an account?') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>
