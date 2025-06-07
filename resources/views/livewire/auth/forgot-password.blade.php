<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

// Se define el layout que utilizará este componente.
new #[Layout('components.layouts.auth')] class extends Component {
    // Propiedad para almacenar la dirección de correo electrónico del usuario.
    public string $email = '';

    /**
     * Envía un enlace de restablecimiento de contraseña a la dirección de correo electrónico proporcionada.
     * Valida que el campo 'email' sea requerido, de tipo string y tenga un formato de correo electrónico válido.
     * Luego, utiliza el facade 'Password' para enviar el enlace de restablecimiento.
     * Finalmente, almacena un mensaje de estado en la sesión para informar al usuario.
     *
     * @return void
     */
    public function sendPasswordResetLink(): void
    {
        // Valida que la dirección de correo electrónico haya sido ingresada y tenga un formato válido.
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // Envía el enlace de restablecimiento de contraseña a la dirección de correo electrónico proporcionada.
        Password::sendResetLink($this->only('email'));

        // Almacena un mensaje de estado en la sesión para informar al usuario
        // que se enviará un enlace de restablecimiento si la cuenta existe.
        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('¿Olvidaste tu contraseña?')"
        :description="__('Ingresa tu correo electrónico para recibir un enlace de restablecimiento')"
    />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <flux:input
            wire:model="email"
            :label="__('Correo electrónico')"
            type="email"
            required
            autofocus
            placeholder="correo@ejemplo.com"
        />

        <flux:button variant="primary" type="submit" class="w-full">
            {{ __('Enviar enlace de restablecimiento') }}
        </flux:button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        {{ __('O vuelve a') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('iniciar sesión') }}</flux:link>
    </div>
</div>