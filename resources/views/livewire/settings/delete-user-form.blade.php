<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

// Este componente Livewire (Volt) permite al usuario eliminar su cuenta.
new class extends Component {
    // Propiedad para almacenar la contraseña ingresada por el usuario para confirmar la eliminación.
    public string $password = '';

    /**
     * Elimina el usuario actualmente autenticado.
     * Valida que la contraseña ingresada sea la contraseña actual del usuario.
     * Si la validación es exitosa, cierra la sesión del usuario y luego elimina su cuenta.
     * Finalmente, redirige al usuario a la página de inicio.
     *
     * @param Logout $logout La acción para cerrar la sesión del usuario.
     * @return void
     */
    public function deleteUser(Logout $logout): void
    {
        // Valida que la contraseña ingresada coincida con la contraseña actual del usuario.
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        // Cierra la sesión del usuario y luego elimina su cuenta.
        tap(Auth::user(), $logout(...))->delete();

        // Redirige al usuario a la página de inicio.
        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('Eliminar cuenta') }}</flux:heading>
        <flux:subheading>{{ __('Eliminar tu cuenta y todos sus recursos') }}</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            {{ __('Eliminar cuenta') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('¿Estás seguro de que deseas eliminar tu cuenta?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Una vez que tu cuenta sea eliminada, todos sus recursos y datos serán eliminados permanentemente. Por favor, ingresa tu contraseña para confirmar que deseas eliminar tu cuenta de forma permanente.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" :label="__('Contraseña')" type="password" />
            @error('password')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('Eliminar cuenta') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>