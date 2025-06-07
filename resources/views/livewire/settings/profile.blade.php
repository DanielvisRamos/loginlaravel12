<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

// Este componente Livewire (Volt) permite al usuario actualizar su información de perfil.
new class extends Component {
    // Propiedades públicas para almacenar los datos del formulario.
    public string $name = '';
    public string $email = '';
    public string $surname = '';
    public string $CI = '';
    public string $address = '';

    /**
     * Monta el componente al ser inicializado.
     * Carga la información del usuario autenticado para prellenar el formulario.
     *
     * @return void
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->surname = $user->surname;
        $this->email = $user->email;
        $this->CI = $user->CI;
        $this->address = $user->address ?? '';
    }

    /**
     * Actualiza la información del perfil del usuario autenticado.
     * Valida los datos del formulario y luego guarda los cambios en la base de datos.
     * Si el correo electrónico ha cambiado, revoca la verificación del correo electrónico.
     * Despacha un evento ('profile-updated') para mostrar un mensaje de éxito.
     *
     * @return void
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'CI' => ['required', 'string', 'max:15'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Envía una notificación de verificación de correo electrónico al usuario actual.
     * Si el usuario ya ha verificado su correo electrónico, lo redirige al panel de control.
     * Al enviar el correo, establece un mensaje flash de sesión para informar al usuario.
     *
     * @return void
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
};
?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Perfil')" :subheading="__('Actualiza tu nombre y dirección de correo electrónico')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Nombre')" type="text" required autofocus autocomplete="name" />
            @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror

            <flux:input wire:model="surname" :label="__('Apellido')" type="text" required autocomplete="family-name" />
            @error('surname') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror

            <flux:input wire:model="CI" :label="__('CI')" type="text" required autocomplete="off" />
            @error('CI') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror

            <div>
                <flux:input wire:model="email" :label="__('Correo electrónico')" type="email" required autocomplete="email" />
                @error('email') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Tu dirección de correo electrónico no está verificada.') }}

                            <flux:link class="text-sm cursor-pointer"
                                wire:click.prevent="resendVerificationNotification">
                                {{ __('Haz clic aquí para reenviar el correo de verificación.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <flux:input wire:model="address" :label="__('Dirección')" type="text" autocomplete="address" />
            @error('address') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Guardar') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Guardado.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>