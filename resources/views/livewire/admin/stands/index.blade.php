<?php

use App\Models\Event; // Importamos el modelo Event
use App\Models\Stand; // Importamos el modelo Stand
use Illuminate\Support\Facades\Validator; // Importamos la fachada Validator para validación manual
use Livewire\Volt\Component; // Importamos la clase Component de Livewire Volt

new class extends Component {
    // Propiedad para almacenar el ID del evento seleccionado en el dropdown
    public ?int $eventoSeleccionado = null;
    // Array para almacenar la lista de eventos disponibles para seleccionar
    public array $eventos = [];
    // Array para almacenar los datos de los nuevos stands a crear (formulario dinámico)
    public array $stands = [];
    // Booleano para controlar el estado de guardado (deshabilitar botón, mostrar spinner)
    public bool $isSaving = false;
    // Cadena para mostrar mensajes de éxito o error al usuario
    public string $message = '';

    /**
     * Método que se ejecuta al inicializar el componente.
     * Carga los eventos activos (en curso) desde la base de datos.
     *
     * @return void
     */
    public function mount(): void
    {
        // Se adapta la consulta para usar 'status' en lugar de 'estado' y la constante STATUS_ONGOING
        $this->eventos = Event::where('status', Event::STATUS_ONGOING)->get()->toArray();
    }

    /**
     * Método que se ejecuta cuando cambia el valor de $eventoSeleccionado.
     * Limpia el array de stands para el formulario, preparando para nuevos stands.
     *
     * @return void
     */
    public function updatedEventoSeleccionado(): void
    {
        // Limpiamos el array de stands para que el formulario esté vacío al cambiar de evento
        $this->stands = [];
    }

    /**
     * Agrega un nuevo conjunto de campos para un stand al formulario dinámico.
     *
     * @return void
     */
    public function agregarStand(): void
    {
        // Si no se ha seleccionado un evento, se muestra un mensaje de error
        if (!$this->eventoSeleccionado) {
            session()->flash('error', 'Debe seleccionar un evento para agregar stands.');
            return;
        }

        // Añadimos un nuevo array de stand con valores por defecto al array $stands
        $this->stands[] = [
            'name' => '',
            'price' => '',
            // Se adapta para usar 'status' y la constante STATUS_AVAILABLE del modelo Stand
            'status' => Stand::STATUS_AVAILABLE,
        ];
    }

    /**
     * Quita un stand del formulario dinámico basándose en su índice.
     *
     * @param int $index El índice del stand a eliminar.
     * @return void
     */
    public function quitarStand(int $index): void
    {
        // Eliminamos el elemento del array
        unset($this->stands[$index]);
        // Reindexamos el array para evitar problemas con los índices de Livewire
        $this->stands = array_values($this->stands);
    }

    /**
     * Guarda los stands creados en el formulario dinámico en la base de datos.
     *
     * @return void
     */
    public function guardarStands(): void
    {
        $this->isSaving = true; // Activamos el estado de guardado

        // Obtenemos solo los nombres de los stands para verificar duplicados
        $standNames = array_column($this->stands, 'name');
        // Encontramos nombres duplicados
        $duplicados = array_diff_assoc($standNames, array_unique($standNames));

        // Si hay nombres duplicados, mostramos un mensaje de error y detenemos el proceso
        if (!empty($duplicados)) {
            $this->message = 'Existen nombres de stands duplicados. Por favor, corrígelos antes de guardar.';
            $this->isSaving = false;
            return;
        }

        // Iteramos sobre cada stand en el array para guardarlo
        foreach ($this->stands as $stand) {
            // Validamos cada stand individualmente
            $validator = Validator::make($stand, [
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                // Se adapta la validación para usar 'status' y las constantes del modelo Stand
                'status' => 'required|in:' . Stand::STATUS_AVAILABLE . ',' . Stand::STATUS_RESERVED . ',' . Stand::STATUS_OCCUPIED,
            ]);

            // Si la validación falla para un stand, lo saltamos y continuamos con el siguiente
            if ($validator->fails()) continue;

            // Creamos el stand en la base de datos
            Stand::create([
                'event_id' => $this->eventoSeleccionado, // Asignamos el ID del evento seleccionado
                'name' => $stand['name'],
                'price' => $stand['price'],
                // Se adapta para usar 'status' al guardar en la base de datos
                'status' => $stand['status'],
            ]);
        }

        // Limpiamos el array de stands después de guardar exitosamente
        $this->stands = [];
        // Mostramos un mensaje de éxito
        $this->message = 'Los stands han sido registrados exitosamente.';
        // Desactivamos el estado de guardado
        $this->isSaving = false;
    }
};

?>

<section class="w-full space-y-6">
    @include('partials.stands-heading')


    <div class="space-y-4">
        <flux:select wire:model.live="eventoSeleccionado" :label="__('Seleccionar evento')" required>
            <option value="">-- Seleccione --</option>
            @foreach ($eventos as $evento)
                <option value="{{ $evento['id'] }}">{{ $evento['name'] }}</option>
            @endforeach
        </flux:select>

        @if (session()->has('error'))
            <div class="text-red-600 mt-2">{{ session('error') }}</div>
        @endif

        @if ($message)
            <div class="text-green-600 mt-2">{{ $message }}</div>
        @endif

        @foreach ($stands as $index => $stand)
            <div class="flex flex-col md:flex-row gap-3 items-end">
                <flux:input wire:model="stands.{{ $index }}.name" :label="__('Nombre del stand')" type="text" />
                <flux:input wire:model="stands.{{ $index }}.price" :label="__('Precio')" type="number" step="0.01" />
                <flux:select
                    wire:model="stands.{{ $index }}.status" {{-- Se adapta el wire:model a 'status' --}}
                    :label="__('Estado')"
                >
                    <option value="{{ \App\Models\Stand::STATUS_AVAILABLE }}">{{ __('Disponible') }}</option>
                    <option value="{{ \App\Models\Stand::STATUS_RESERVED }}">{{ __('Reservado') }}</option>
                    <option value="{{ \App\Models\Stand::STATUS_OCCUPIED }}">{{ __('Ocupado') }}</option>
                </flux:select>
                <flux:button wire:click="quitarStand({{ $index }})" variant="danger" size="sm">
                    - {{ __('Quitar') }}
                </flux:button>
            </div>
        @endforeach

        <flux:button variant="primary" wire:click="agregarStand" class="mt-2">
            + {{ __('Agregar Stand') }}
        </flux:button>

        <flux:button variant="primary" wire:click="guardarStands" class="mt-2" :disabled="$isSaving">
            {{ __('Guardar Stands') }}
        </flux:button>
    </div>
</section>