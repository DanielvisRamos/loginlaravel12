<?php

use App\Models\Event;
use App\Models\Stand;
use Illuminate\Support\Facades\Validator;
use Livewire\Volt\Component;

new class extends Component {
    public ?int $eventoSeleccionado = null;
    public array $eventos = [];
    public array $stands = []; // Solo para nuevos stands (formulario)
    public bool $isSaving = false;
    public string $message = '';

    public function mount(): void
    {
        $this->eventos = Event::where('estado', Event::ESTADO_CURSANDO)->get()->toArray();
    }

    public function updatedEventoSeleccionado(): void
    {
        // Ya no se cargan los stands existentes al array de formulario
        $this->stands = [];
    }

    public function agregarStand(): void
    {
        if (!$this->eventoSeleccionado) {
            session()->flash('error', 'Debe seleccionar un evento para agregar stands.');
            return;
        }

        $this->stands[] = [
            'name' => '',
            'price' => '',
            'estado' => Stand::ESTADO_DISPONIBLE,
        ];
    }

    public function quitarStand(int $index): void
    {
        unset($this->stands[$index]);
        $this->stands = array_values($this->stands); // Reindexar
    }

    public function guardarStands(): void
    {
        $this->isSaving = true;

        $standNames = array_column($this->stands, 'name');
        $duplicados = array_diff_assoc($standNames, array_unique($standNames));

        if (!empty($duplicados)) {
            $this->message = 'Existen nombres de stands duplicados. Por favor, corrígelos antes de guardar.';
            $this->isSaving = false;
            return;
        }

        foreach ($this->stands as $stand) {
            $validator = Validator::make($stand, [
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) continue;

            Stand::create([
                'event_id' => $this->eventoSeleccionado,
                'name' => $stand['name'],
                'price' => $stand['price'],
                'estado' => $stand['estado'],
            ]);
        }

        $this->stands = [];
        $this->message = 'Los stands han sido registrados exitosamente.';
        $this->isSaving = false;
    }
};

?>



<section class="w-full space-y-6">
    @include('partials.stands-heading')
    <x-settings.layoutstands :heading="__('Gestión de Stands')" :subheading="__('Selecciona un evento y gestiona sus stands')">
        

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
                    <flux:text class="mt-2">{{ ucfirst($stand['estado']) }}</flux:text>
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
    </x-settings.layout>
</section>


