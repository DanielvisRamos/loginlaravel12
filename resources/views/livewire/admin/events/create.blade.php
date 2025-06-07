<?php

use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate; // Asegúrate de que esta línea esté presente

new class extends Component {
    #[Validate('array')]
    public array $eventos = [];
    public bool $isSaving = false;

    public function mount(): void
    {
        // Agrega un formulario por defecto al cargar el componente si no hay ninguno.
        if (empty($this->eventos)) {
            $this->agregarEvento();
        }
    }

    /**
     * Agrega un nuevo formulario vacío para un evento al array $eventos.
     *
     * @return void
     */
    public function agregarEvento(): void
    {
        $this->eventos[] = [
            'name' => '',
            'description' => '',
            'address' => '',
            'start_date' => '',
            'end_date' => '',
        ];
    }

    /**
     * Elimina un formulario de evento del array $eventos basándose en su índice.
     * Asegura que siempre haya al menos un formulario después de eliminar.
     *
     * @param int $index El índice del evento a quitar.
     * @return void
     */
    public function quitarEvento(int $index): void
    {
        unset($this->eventos[$index]);
        $this->eventos = array_values($this->eventos); // Reindexar el array

        // Asegurar que siempre haya al menos un formulario
        if (empty($this->eventos)) {
            $this->agregarEvento();
        }
    }

    /**
     * Hook de Livewire que se ejecuta cuando una propiedad es actualizada.
     * Se utiliza para la validación en tiempo real de campos individuales.
     *
     * @param string $propertyName El nombre de la propiedad que fue actualizada.
     * @return void
     */
    public function updated($propertyName): void
    {
        // Verifica si la propiedad actualizada pertenece a un evento específico.
        if (str_starts_with($propertyName, 'eventos.')) {
            $parts = explode('.', $propertyName);
            // Asegura que la propiedad tiene el formato 'eventos.INDEX.FIELD_NAME'
            if (count($parts) === 3) {
                $index = $parts[1];
                $field = $parts[2];
                // Valida solo el campo específico del evento específico.
                $this->validateOnly("eventos.{$index}.{$field}");
            }
        }
    }

    /**
     * Guarda los eventos definidos en el array $eventos en la base de datos.
     * Realiza la validación antes de guardar y asigna el ID del usuario autenticado como creador.
     * El estado del evento se determina automáticamente según las fechas.
     *
     * @return void
     */
    public function guardarEvento(): void
    {
        $this->isSaving = true; // Indica que el proceso de guardado está en curso.

        try {
            $this->validate(); // Ejecuta todas las reglas de validación definidas.

            foreach ($this->eventos as $evento) {
                // Determina el estado del evento basado en las fechas de inicio y fin.
                $status = now() >= $evento['start_date'] && now() <= $evento['end_date']
                    ? Event::STATUS_ONGOING // Si la fecha actual está entre inicio y fin, está en curso.
                    : (now() > $evento['end_date'] ? Event::STATUS_COMPLETED : Event::STATUS_ONGOING); // Si ya pasó la fecha de fin, está culminado; de lo contrario, en curso.

                // Crea un nuevo registro de Event en la base de datos.
                Event::create([
                    'name' => $evento['name'],
                    'description' => $evento['description'],
                    'address' => $evento['address'],
                    'start_date' => $evento['start_date'],
                    'end_date' => $evento['end_date'],
                    'status' => $status,
                    'created_by' => Auth::id(), // Asigna el ID del usuario actualmente autenticado.
                ]);
            }

            $this->eventos = []; // Limpia el array de eventos después de guardar.
            $this->agregarEvento(); // Agrega un nuevo formulario vacío para futuras creaciones.
            $this->dispatch('saved'); // Emite un evento 'saved' para notificar a la interfaz de usuario.
        } finally {
            $this->isSaving = false; // Asegura que $isSaving se restablezca.
        }
    }

    /**
     * Define las reglas de validación para cada campo de cada evento.
     *
     * @return array
     */
    protected function rules(): array
    {
        $rules = [];
        // Itera sobre el array de eventos para construir reglas de validación dinámicas.
        foreach ($this->eventos as $index => $event) {
            $rules["eventos.{$index}.name"] = 'required|string|max:255';
            $rules["eventos.{$index}.description"] = 'required|string';
            $rules["eventos.{$index}.address"] = 'required|string';
            $rules["eventos.{$index}.start_date"] = 'required|date';
            // La fecha de fin debe ser igual o posterior a la fecha de inicio del mismo evento.
            $rules["eventos.{$index}.end_date"] = 'required|date|after_or_equal:eventos.' . $index . '.start_date';
        }
        return $rules;
    }

    /**
     * Define los nombres de atributo personalizados para los mensajes de validación.
     * Esto hace que los mensajes sean más legibles para el usuario.
     *
     * @return array
     */
    protected function getValidationAttributes(): array
    {
        return [
            'eventos.*.name' => __('Nombre del evento'),
            'eventos.*.description' => __('Descripción del evento'),
            'eventos.*.address' => __('Dirección del evento'),
            'eventos.*.start_date' => __('Fecha Inicio'),
            'eventos.*.end_date' => __('Fecha Fin'),
        ];
    }
};
?>

<section class="w-full space-y-6">
    @include('partials.events-heading')

    <div class="space-y-4">
        @foreach ($eventos as $index => $evento)
            <div class="relative overflow-hidden rounded-xl border border-border bg-transparent text-card-foreground p-6">
                <flux:input wire:model.live="eventos.{{ $index }}.name" :label="__('Nombre del evento')" type="text" />

                <flux:input wire:model.live="eventos.{{ $index }}.description" :label="__('Descripción del evento')" type="text" />
                
                <flux:input wire:model.live="eventos.{{ $index }}.address" :label="__('Dirección del evento')" type="text" />
                
                <flux:input wire:model.live="eventos.{{ $index }}.start_date" :label="__('Fecha Inicio')" type="datetime-local" />
                
                <flux:input wire:model.live="eventos.{{ $index }}.end_date" :label="__('Fecha Fin')" type="datetime-local" />
                
                <flux:button wire:click="quitarEvento({{ $index }})" variant="danger" size="sm" class="p-2 mt-2">
                    - {{ __('Quitar') }}
                </flux:button>
            </div>
            <flux:separator variant="subtle" />
        @endforeach

        <flux:button variant="primary" wire:click="agregarEvento" class="mt-2">
            + {{ __('Agregar Evento') }}
        </flux:button>

        @if ($eventos)
            <flux:button variant="primary" wire:click="guardarEvento" class="mt-2" :disabled="$isSaving">
                {{ __('Guardar Eventos') }}
            </flux:button>
        @endif

        <x-action-message class="ml-5" on="saved">
            {{ __('Guardado.') }}
        </x-action-message>
    </div>
</section>