<?php

use App\Models\User;
use App\Models\Phone;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

// Se define el layout que utilizará este componente.
new #[Layout('components.layouts.auth')] class extends Component {
    // Se definen las propiedades del componente para el formulario de registro.
    public string $name = '';
    public string $surname = '';
    public string $ci_prefix = 'V'; // Nueva propiedad para el prefijo de la CI (V o E)
    public string $CI = ''; // Ahora solo almacenará el número de la CI
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $address = '';
    public bool $terms_accepted = false;
    public ?string $full_ci = null;

    /** @var array Lista de teléfonos dinámicos */
    public array $phones = ['']; // Se inicializa con un campo vacío para el primer teléfono.

    /**
     * Agrega un nuevo campo de entrada para un número de teléfono.
     * Este método se llama generalmente mediante un botón en la interfaz de usuario.
     *
     * @return void
     */
    public function addPhone(): void
    {
        $this->phones[] = '';
    }

    /**
     * Elimina un campo de entrada de teléfono en el índice especificado.
     * Después de eliminar, reindexa el array de teléfonos para evitar huecos.
     *
     * @param int $index El índice del teléfono a eliminar.
     * @return void
     */
    public function removePhone(int $index): void
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones); // Reindexa el array después de eliminar.
    }

    /**
     * Procesa la solicitud de registro de un nuevo usuario.
     * Valida los datos del formulario, crea un nuevo usuario en la base de datos,
     * guarda sus números de teléfono asociados, inicia sesión al usuario registrado
     * y lo redirige al dashboard de emprendedor.
     *
     * @return void
     */
    public function register(): void
    {
        // Valida los campos del formulario de registro.
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'ci_prefix' => ['required', 'string', 'in:V,E'], // Valida el prefijo
            'CI' => ['required', 'string', 'min:7', 'max:20'], // Valida solo el número de CI
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()],
            'address' => ['nullable', 'string', 'max:255'],
            'phones' => ['required', 'array', 'min:1'],
            'phones.*' => ['required', 'string', 'min:7', 'max:20'],
            'terms_accepted' => ['required', 'accepted'],
        ]);

        // Combina el prefijo y el número de CI para formar el valor completo
        $full_ci = $this->ci_prefix . '-' . $this->CI;

        // Valida la unicidad de la CI completa después de combinarla
        $this->validate([
            'full_ci' => ['unique:' . User::class . ',CI'], // 'CI' es el nombre de la columna en la BD
        ], [
            'full_ci.unique' => 'La Cédula de Identidad ingresada ya está registrada.',
        ], [
            'full_ci' => 'Cédula de Identidad', // Nombre amigable para el error
        ]);


        // Hashea la contraseña antes de guardarla en la base de datos.
        $validated['password'] = Hash::make($validated['password']);
        // Establece el estado del usuario a activo al registrarse.
        $validated['status'] = User::STATUS_ACTIVE;
        // Asigna el rol de 'emprendedor' al usuario registrado (asumiendo que el ID 2 corresponde a este rol).
        $validated['role_id'] = 2;
        // Asigna la CI completa al campo 'CI' del usuario
        $validated['CI'] = $full_ci;

        // Crea un nuevo usuario en la base de datos y dispara el evento Registered.
        event(new Registered(($user = User::create($validated))));

        // Guarda los números de teléfono asociados al usuario.
        foreach ($this->phones as $phoneNumber) {
            Phone::create([
                'user_id' => $user->id,
                'phone_number' => $phoneNumber,
                'status' => Phone::STATUS_ACTIVE,
            ]);
        }

        // Inicia la sesión del usuario recién registrado.
        Auth::login($user);

        // Redirige al usuario al dashboard de emprendedor.
        $this->redirectIntended(route('entrepreneur.dashboard'), navigate: true);
    }
};
?>
<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Crear una cuenta')" :description="__('Ingresa tus datos a continuación para registrarte')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <flux:input wire:model.live.live="name" :label="__('Nombre')" type="text" required autofocus autocomplete="name"
            :placeholder="__('Nombre completo')" />
        @error('name')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <flux:input wire:model.live="surname" :label="__('Apellido')" type="text" required autocomplete="surname"
            :placeholder="__('Apellido completo')" />
        @error('surname')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <div>
            <label for="ci_input" class="block text-sm font-medium text-zinc-700 dark:text-white mb-1">{{ __('Cédula de Identidad') }}</label>
            <div class="flex gap-2">
                <select wire:model.live="ci_prefix" class="form-select rounded-md shadow-sm border-gray-300 dark:bg-zinc-800 dark:border-zinc-700 dark:text-white focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-20">
                    <option value="V">V</option>
                    <option value="E">E</option>
                </select>
                <flux:input wire:model.live="CI" type="number" required autocomplete="off"
                    :placeholder="__('Número de CI (ej: 12345678)')" class="flex-1" id="ci_input" />
            </div>
            @error('ci_prefix')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
            @error('CI')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
            @error('full_ci') {{-- Error para la unicidad de la CI completa --}}
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>


        <flux:input wire:model.live="email" :label="__('Correo electrónico')" type="email" required autocomplete="email"
            placeholder="correo@ejemplo.com" />
        @error('email')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <div>
            <flux:input wire:model.live="password" :label="__('Contraseña')" type="password" required autocomplete="new-password"
                :placeholder="__('Contraseña')" id="password" />
            @error('password')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <flux:input wire:model.live="password_confirmation" :label="__('Confirmar contraseña')" type="password" required
            autocomplete="new-password" :placeholder="__('Confirmar contraseña')" />
        @error('password_confirmation')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror
        <flux:input wire:model.live="address" :label="__('Dirección')" type="text" optional autocomplete="address"
            :placeholder="__('Tu dirección (opcional)')" />
        @error('address')
            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
        @enderror

        <div class="flex flex-col gap-2">
            <label class="text-sm font-medium text-zinc-700 dark:text-white">{{ __('Teléfonos') }}</label>

            @foreach ($phones as $index => $phone)
                <div class="flex items-center gap-2">
                    <flux:input wire:model.live="phones.{{ $index }}" type="text" placeholder="Número de teléfono"
                        required />
                    <flux:button type="button" variant="danger" class="px-3 py-2"
                        wire:click="removePhone({{ $index }})">
                        -
                    </flux:button>
                </div>
                @error('phones.' . $index)
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror
            @endforeach

            <flux:button type="button" variant="primary" class="mt-2 w-fit" wire:click="addPhone">
                + {{ __('Agregar Teléfono') }}
            </flux:button>
        </div>

        <div>
            <label class="flex items-center gap-2">
                <input wire:model.live="terms_accepted" id="terms_accepted" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" required>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Acepto los
                    <a href="{{ asset('terminos_condiciones.pdf') }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 underline dark:text-[var(--color-primary)] dark:hover:text-[var(--color-primary)]/80">
                        términos y condiciones
                    </a>
                </span>
            </label>
            @error('terms_accepted')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Crear cuenta') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('¿Ya tienes una cuenta?') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('Inicia sesión') }}</flux:link>
    </div>
</div>