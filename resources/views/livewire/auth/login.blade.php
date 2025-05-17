<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Illuminate\Validation\ValidationException;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
{
    $this->validate();

    if (! Auth::attempt([
        'email' => $this->email,
        'password' => $this->password,
        'estado' => 'activo',
    ], $this->remember)) {
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    session()->regenerate();

    $user = Auth::user();

    // Verificamos el rol y redirigimos según el caso
    if ($user->role?->name === 'admin') {
        $this->redirectIntended(route('dashboard'));
    } elseif ($user->role?->name === 'emprendedor') {
        $this->redirectIntended(route('dashboard.entrepreneur'));
    }
}

};
?>


<!-- ======================== BLADE ========================= -->
<div class="flex flex-col gap-6">
    <x-auth-header 
        :title="__('Inicia sesión en tu cuenta')" 
        :description="__('Ingresa tu correo electrónico y contraseña para continuar')" 
    />

    <x-auth-session-status class="text-center text-[var(--color-foreground)]" :status="session('status')" />

    <form wire:submit.prevent="login" class="flex flex-col gap-6">
        <!-- Correo electrónico -->
        <flux:input
            wire:model="email"
            :label="__('Correo electrónico')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="correo@ejemplo.com"
            class="[&_[data-flux-label]]:text-[var(--color-foreground)] [&_input]:bg-[var(--color-input)] [&_input]:text-[var(--color-foreground)] [&_input]:border-[var(--color-border)]"
        />
        @error('email')
            <p class="text-sm text-[var(--color-destructive)] mt-1">{{ $message }}</p>
        @enderror

        <!-- Contraseña -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Contraseña')"
                type="password"
                required
                autocomplete="current-password"
                placeholder="******"
                class="[&_[data-flux-label]]:text-[var(--color-foreground)] [&_input]:bg-[var(--color-input)] [&_input]:text-[var(--color-foreground)] [&_input]:border-[var(--color-border)]"
            />
            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-sm text-[var(--color-primary)] hover:text-[var(--color-primary)]/80" :href="route('password.request')" wire:navigate>
                    {{ __('¿Olvidaste tu contraseña?') }}
                </flux:link>
            @endif
        </div>
        @error('password')
            <p class="text-sm text-[var(--color-destructive)] mt-1">{{ $message }}</p>
        @enderror

        <!-- Recordarme -->
        <flux:checkbox 
            wire:model="remember" 
            :label="__('Recordarme')"
            class="[&_[data-flux-label]]:text-[var(--color-foreground)]"
        />

        <!-- Botón de inicio de sesión -->
        <div class="flex items-center justify-end">
            <flux:button 
                variant="primary" 
                type="submit" 
                class="w-full bg-[var(--color-primary)] text-[var(--color-primary-foreground)] hover:bg-[var(--color-primary)]/90"
            >
                {{ __('Iniciar sesión') }}
            </flux:button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="text-center text-sm text-[var(--color-muted-foreground)]">
            {{ __('¿No tienes una cuenta?') }}
            <flux:link 
                :href="route('register')" 
                wire:navigate
                class="text-[var(--color-primary)] hover:text-[var(--color-primary)]/80"
            >
                {{ __('Regístrate') }}
            </flux:link>
        </div>
    @endif
</div>