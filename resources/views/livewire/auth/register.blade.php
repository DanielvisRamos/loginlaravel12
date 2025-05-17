<?php

use App\Models\User;
use App\Models\Phone;
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

    /** @var array Lista de teléfonos dinámicos */
    public array $phones = ['']; // <- Inicializamos con un campo vacío

    /**
     * Agrega un nuevo input de teléfono
     */
    public function addPhone()
    {
        $this->phones[] = '';
    }

    /**
     * Elimina un input de teléfono
     */
    public function removePhone($index)
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones); // Reindexar el array
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'CI' => ['required', 'string', 'min:7', 'max:20', 'unique:' . User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'address' => ['nullable', 'string', 'max:255'],
            'phones' => ['required', 'array', 'min:1'], // Al menos 1 teléfono
            'phones.*' => ['required', 'string', 'min:7', 'max:20'], // Validar cada teléfono
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['estado'] = User::ESTADO_ACTIVO;
        $validated['role_id'] = 2;

        // Guardar el usuario
        event(new Registered(($user = User::create($validated))));

        // Guardar los teléfonos asociados
        foreach ($this->phones as $phoneNumber) {
            Phone::create([
                'user_id' => $user->id,
                'phone_number' => $phoneNumber,
                'estado' => Phone::ESTADO_ACTIVO,
            ]);
        }

        // Iniciar sesión
        Auth::login($user);

        // Redirigir al dashboard
        $this->redirectIntended(route('dashboard.entrepreneur', absolute: false), navigate: true);
    }
};
?>
<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Crear una cuenta')" :description="__('Ingresa tus datos a continuación para registrarte')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Nombre -->
        <flux:input wire:model="name" :label="__('Nombre')" type="text" required autofocus autocomplete="name"
            :placeholder="__('Nombre completo')" />

        <!-- Apellido -->
        <flux:input wire:model="surname" :label="__('Apellido')" type="text" required autocomplete="surname"
            :placeholder="__('Apellido completo')" />

        <!-- CI -->
        <flux:input wire:model="CI" :label="__('CI')" type="number" required autocomplete="off"
            :placeholder="__('Ingresa tu CI (ej: 12345678)')" />
        @error('CI')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Correo electrónico -->
        <flux:input wire:model="email" :label="__('Correo electrónico')" type="email" required autocomplete="email"
            placeholder="correo@ejemplo.com" />
        @error('email')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Contraseña -->
        <flux:input wire:model="password" :label="__('Contraseña')" type="password" required autocomplete="new-password"
            :placeholder="__('Contraseña')" />
        @error('password')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Confirmar contraseña -->
        <flux:input wire:model="password_confirmation" :label="__('Confirmar contraseña')" type="password" required
            autocomplete="new-password" :placeholder="__('Confirmar contraseña')" />
        @error('password_confirmation')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror
        <!-- Dirección -->
        <flux:input wire:model="address" :label="__('Dirección')" type="text" optional autocomplete="address"
            :placeholder="__('Tu dirección (opcional)')" />
        @error('address')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <!-- Teléfonos -->
        <div class="flex flex-col gap-2">
            <label class="text-sm font-medium text-zinc-700 dark:text-white">{{ __('Teléfonos') }}</label>

            @foreach ($phones as $index => $phone)
                <div class="flex items-center gap-2">
                    <flux:input wire:model="phones.{{ $index }}" type="text" placeholder="Número de teléfono"
                        required />
                    <flux:button type="button" variant="danger" class="px-3 py-2"
                        wire:click="removePhone({{ $index }})">
                        -
                    </flux:button>
                </div>
                @error('phones.' . $index)
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror
            @endforeach

            <!-- Botón de agregar teléfono -->
            <flux:button type="button" variant="primary" class="mt-2 w-fit" wire:click="addPhone">
                + {{ __('Agregar Teléfono') }}
            </flux:button>
        </div>

        <!-- Botón para crear cuenta -->
        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Crear cuenta') }}
            </flux:button>
        </div>
    </form>

    <!-- Ya tienes cuenta -->
    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('¿Ya tienes una cuenta?') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('Inicia sesión') }}</flux:link>
    </div>
</div>