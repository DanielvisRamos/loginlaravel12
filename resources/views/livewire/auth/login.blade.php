<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Illuminate\Validation\ValidationException;

// Se define el layout que utilizará este componente.
new #[Layout('components.layouts.auth')] class extends Component {
    // Se definen las propiedades del componente y sus reglas de validación.
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Intenta autenticar al usuario.
     * Valida las entradas del formulario y luego intenta iniciar sesión
     * utilizando las credenciales proporcionadas y el estado 'activo'.
     * Si la autenticación falla, lanza una excepción de validación.
     * Si tiene éxito, regenera la sesión y redirige al usuario
     * a su panel de control correspondiente según su rol.
     *
     * @return void
     */
    public function login(): void
    {
        $this->validate();

        // Intenta autenticar al usuario con el email, la contraseña
        // y verifica que el estado del usuario sea 'activo'.
        if (! Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
            'status' => 'active', // Se cambió 'estado' a 'status' para coincidir con el modelo User.
        ], $this->remember)) {
            // Si la autenticación falla, lanza una excepción con un mensaje de error.
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Regenera la ID de la sesión para prevenir la fijación de sesión.
        session()->regenerate();

        // Obtiene el usuario autenticado.
        $user = Auth::user();

        // Verifica el rol del usuario y lo redirige al dashboard correspondiente.
        if ($user->role?->name === 'admin') {
            // Redirige al dashboard de administrador definido en la ruta 'dashboard'.
            $this->redirectIntended(route('admin.dashboard')); // Se actualizó el nombre de la ruta a 'admin.dashboard'.
        } elseif ($user->role?->name === 'emprendedor') {
            // Redirige al dashboard de emprendedor definido en la ruta 'dashboard.entrepreneur'.
            $this->redirectIntended(route('entrepreneur.dashboard')); // Se actualizó el nombre de la ruta a 'entrepreneur.dashboard'.
        }
    }
};
?>

<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Inicia sesión en tu cuenta')"
        :description="__('Ingresa tu correo electrónico y contraseña para continuar')"
    />

    <x-auth-session-status class="text-center text-[var(--color-foreground)]" :status="session('status')" />

    <form wire:submit.prevent="login" class="flex flex-col gap-6">
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

        <flux:checkbox
            wire:model="remember"
            :label="__('Recordarme')"
            class="[&_[data-flux-label]]:text-[var(--color-foreground)]"
        />

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