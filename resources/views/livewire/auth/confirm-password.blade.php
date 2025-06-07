<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

// Se define el layout que utilizará este componente.
new #[Layout('components.layouts.auth')] class extends Component {
    // Propiedad para almacenar la contraseña ingresada por el usuario.
    public string $password = '';

    /**
     * Confirma la contraseña del usuario actualmente autenticado.
     * Valida que el campo 'password' sea requerido y de tipo string.
     * Luego, verifica si la contraseña ingresada coincide con la del usuario autenticado.
     * Si la validación falla o la contraseña es incorrecta, lanza una excepción.
     * Si la confirmación es exitosa, marca la contraseña como confirmada en la sesión
     * y redirige al usuario a la ruta 'dashboard' correspondiente a su rol.
     *
     * @return void
     */
    public function confirmPassword(): void
    {
        // Valida que la contraseña haya sido ingresada.
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        // Verifica si la contraseña proporcionada coincide con la del usuario autenticado.
        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            // Si la contraseña no coincide, lanza una excepción de validación con un mensaje de error.
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        // Marca la contraseña como confirmada en la sesión.
        session(['auth.password_confirmed_at' => time()]);

        // Redirige al usuario al dashboard correspondiente a su rol.
        $user = Auth::user();
        if ($user->role?->name === 'admin') {
            $this->redirectIntended(default: route('admin.dashboard'), navigate: true);
        } elseif ($user->role?->name === 'emprendedor') {
            $this->redirectIntended(default: route('entrepreneur.dashboard'), navigate: true);
        } else {
            // Redirección por defecto si el rol no es admin ni emprendedor.
            $this->redirectIntended(default: route('home'), navigate: true);
        }
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Confirmar contraseña')"
        :description="__('Esta es un área segura de la aplicación. Por favor, confirma tu contraseña antes de continuar.')"
    />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="confirmPassword" class="flex flex-col gap-6">
        <flux:input
            wire:model="password"
            :label="__('Contraseña')"
            type="password"
            required
            autocomplete="current-password"
            :placeholder="__('Contraseña')"
        />

        <flux:button variant="primary" type="submit" class="w-full">
            {{ __('Confirmar') }}
        </flux:button>
    </form>
</div>