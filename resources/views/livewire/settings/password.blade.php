<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

// Este componente Livewire (Volt) permite al usuario actualizar su contraseña.
new class extends Component {
    // Propiedad para almacenar la contraseña actual del usuario.
    public string $current_password = '';
    // Propiedad para almacenar la nueva contraseña que el usuario desea establecer.
    public string $password = '';
    // Propiedad para almacenar la confirmación de la nueva contraseña.
    public string $password_confirmation = '';

    /**
     * Actualiza la contraseña del usuario actualmente autenticado.
     * Valida que la contraseña actual sea correcta y que la nueva contraseña cumpla con los requisitos
     * y su confirmación coincida. Si la validación falla, resetea los campos de contraseña y lanza la excepción.
     * Si la validación es exitosa, actualiza la contraseña del usuario en la base de datos y luego resetea los campos.
     * Finalmente, despacha un evento ('password-updated') para mostrar un mensaje de éxito.
     *
     * @return void
     */
    public function updatePassword(): void
    {
        try {
            // Valida los campos del formulario de cambio de contraseña.
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            // Si la validación falla, resetea los campos de contraseña.
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        // Actualiza la contraseña del usuario en la base de datos.
        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Resetea los campos del formulario después de la actualización exitosa.
        $this->reset('current_password', 'password', 'password_confirmation');

        // Despacha un evento para indicar que la contraseña ha sido actualizada.
        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    {{-- Incluye el encabezado de la sección de configuración (título y subtítulo). --}}
    @include('partials.settings-heading')

    {{-- Utiliza un layout específico para las secciones de configuración. --}}
    <x-settings.layout :heading="__('Actualizar contraseña')" :subheading="__('Asegúrate de que tu cuenta esté utilizando una contraseña larga y aleatoria para mantenerla segura')">
        <form wire:submit="updatePassword" class="mt-6 space-y-6">
            {{-- Campo para la contraseña actual. --}}
            <flux:input
                wire:model="current_password"
                :label="__('Contraseña actual')"
                type="password"
                required
                autocomplete="current-password"
            />
            @error('current_password')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror

            {{-- Campo para la nueva contraseña. --}}
            <flux:input
                wire:model="password"
                :label="__('Nueva contraseña')"
                type="password"
                required
                autocomplete="new-password"
            />
            @error('password')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror

            {{-- Campo para confirmar la nueva contraseña. --}}
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirmar contraseña')"
                type="password"
                required
                autocomplete="new-password"
            />
            @error('password_confirmation')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Guardar') }}</flux:button>
                </div>

                {{-- Mensaje de confirmación que se muestra al actualizar la contraseña. --}}
                <x-action-message class="me-3" on="password-updated">
                    {{ __('Guardado.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>