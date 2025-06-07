<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

// Se define el layout que utilizará este componente.
new #[Layout('components.layouts.auth')] class extends Component {
    // Propiedad protegida que almacena el token de restablecimiento de contraseña.
    #[Locked]
    public string $token = '';
    // Propiedad para almacenar la dirección de correo electrónico del usuario.
    public string $email = '';
    // Propiedad para almacenar la nueva contraseña ingresada por el usuario.
    public string $password = '';
    // Propiedad para almacenar la confirmación de la nueva contraseña.
    public string $password_confirmation = '';

    /**
     * Monta el componente al ser inicializado.
     * Recibe el token de restablecimiento de contraseña desde la URL
     * y también recupera el correo electrónico si está presente en la query string.
     *
     * @param string $token El token de restablecimiento de contraseña.
     * @return void
     */
    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');
    }

    /**
     * Restablece la contraseña del usuario.
     * Valida el token, el correo electrónico y las nuevas contraseñas.
     * Luego, intenta restablecer la contraseña utilizando el facade 'Password'.
     * Si el restablecimiento es exitoso, actualiza la contraseña del usuario en la base de datos,
     * genera un nuevo 'remember_token' y dispara el evento PasswordReset.
     * Finalmente, redirige al usuario a la página de inicio de sesión con un mensaje de éxito
     * o muestra un error si el restablecimiento falla.
     *
     * @return void
     */
    public function resetPassword(): void
    {
        // Valida los campos del formulario de restablecimiento de contraseña.
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Intenta restablecer la contraseña del usuario.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                // Actualiza la contraseña del usuario y genera un nuevo 'remember_token'.
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Dispara el evento PasswordReset.
                event(new PasswordReset($user));
            }
        );

        // Verifica el resultado del restablecimiento de contraseña.
        if ($status != Password::PASSWORD_RESET) {
            // Si hubo un error, añade un mensaje de error al campo 'email'.
            $this->addError('email', __($status));
            return;
        }

        // Si la contraseña se restableció exitosamente, almacena un mensaje de éxito en la sesión
        // y redirige al usuario a la página de inicio de sesión.
        Session::flash('status', __($status));
        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Restablecer contraseña')" :description="__('Por favor, ingresa tu nueva contraseña a continuación')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="resetPassword" class="flex flex-col gap-6">
        <flux:input
            wire:model="email"
            :label="__('Correo electrónico')"
            type="email"
            required
            autocomplete="email"
        />
        @error('email')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
        @enderror

        <flux:input
            wire:model="password"
            :label="__('Contraseña')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Contraseña')"
        />
        @error('password')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
        @enderror

        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirmar contraseña')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirmar contraseña')"
        />
        @error('password_confirmation')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
        @enderror

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Restablecer contraseña') }}
            </flux:button>
        </div>
    </form>
</div>