<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

// Se define el layout que utilizará este componente.
new #[Layout('components.layouts.auth')] class extends Component {
    /**
     * Envía una notificación de verificación de correo electrónico al usuario.
     * Si el usuario ya ha verificado su correo electrónico, lo redirige al dashboard
     * correspondiente a su rol. Si no, envía la notificación y almacena un estado
     * en la sesión para mostrar un mensaje al usuario.
     *
     * @return void
     */
    public function sendVerification(): void
    {
        // Verifica si el usuario ya ha verificado su correo electrónico.
        if (Auth::user()->hasVerifiedEmail()) {
            // Redirige al usuario al dashboard según su rol.
            $user = Auth::user();
            if ($user->role?->name === 'admin') {
                $this->redirectIntended(default: route('admin.dashboard'), navigate: true);
            } elseif ($user->role?->name === 'emprendedor') {
                $this->redirectIntended(default: route('entrepreneur.dashboard'), navigate: true);
            } else {
                $this->redirectIntended(default: route('home'), navigate: true);
            }
            return;
        }

        // Envía la notificación de verificación de correo electrónico al usuario.
        Auth::user()->sendEmailVerificationNotification();

        // Almacena un estado en la sesión para indicar que se ha enviado el enlace de verificación.
        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Cierra la sesión del usuario actual en la aplicación.
     * Utiliza la acción de cierre de sesión proporcionada y luego redirige al usuario a la página de inicio.
     *
     * @param Logout $logout La acción de cierre de sesión.
     * @return void
     */
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="mt-4 flex flex-col gap-6">
    <flux:text class="text-center">
        {{ __('Por favor, verifica tu dirección de correo electrónico haciendo clic en el enlace que acabamos de enviarte.') }}
    </flux:text>

    @if (session('status') == 'verification-link-sent')
        <flux:text class="text-center font-medium !dark:text-green-400 !text-green-600">
            {{ __('Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionaste durante el registro.') }}
        </flux:text>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <flux:button wire:click="sendVerification" variant="primary" class="w-full">
            {{ __('Reenviar correo de verificación') }}
        </flux:button>

        <flux:link class="text-sm cursor-pointer" wire:click="logout">
            {{ __('Cerrar sesión') }}
        </flux:link>
    </div>
</div>