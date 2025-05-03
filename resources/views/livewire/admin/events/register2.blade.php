<?php

use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Volt\Component;

new class extends Component {
    public array $eventos = [];
    public bool $isSaving = false;

    public function agregarEvento()
    {
        $this->eventos[] = [
            'name' => '',
            'description' => '',
            'address' => '',
            'start_date' => '',
            'end_date' => '',
            'estado' => Event::ESTADO_CURSANDO,
        ];
    }

    public function quitarEvento(int $index): void
    {
        unset($this->eventos[$index]);
        $this->eventos = array_values($this->eventos); // Reindexar
    }

    public function rules()
    {
        return [
            'eventos.*.name' => 'required|string|max:255',
            'eventos.*.description' => 'required|string',
            'eventos.*.address' => 'required|string',
            'eventos.*.start_date' => 'required|date',
            'eventos.*.end_date' => 'required|date|after_or_equal:eventos.*.start_date',
            'eventos.*.estado' => 'required|string',
        ];
    }

    public function guardarEvento(): void
{
    $this->isSaving = true;

    try {
        $this->validate();

        foreach ($this->eventos as $evento) {
            Event::create([
                'name' => $evento['name'],
                'description' => $evento['description'],
                'address' => $evento['address'],
                'start_date' => $evento['start_date'],
                'end_date' => $evento['end_date'],
                'estado' => $evento['estado'],
                'created_by' => Auth::id(),
            ]);
        }

        $this->eventos = [];
        $this->dispatch('saved');
    } finally {
        $this->isSaving = false; // ⚡ Esto SIEMPRE se ejecuta
    }
}

};
?>



<section class="w-full space-y-6">
    @include('partials.events-heading')

    <x-layouts.admin.layoutevents>
        <div class="space-y-4">
            @foreach ($eventos as $index => $evento)
                <div class="p-4 space-y-4">
                    <flux:input wire:model="eventos.{{ $index }}.name" :label="__('Nombre del evento')" type="text" />
                    @error('eventos.' . $index . '.name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror

                    <flux:input wire:model="eventos.{{ $index }}.description" :label="__('Descripción del evento')" type="text" />
                    @error('eventos.' . $index . '.description')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror

                    <flux:input wire:model="eventos.{{ $index }}.address" :label="__('Dirección del evento')" type="text" />
                    @error('eventos.' . $index . '.address')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror

                    <flux:input wire:model="eventos.{{ $index }}.start_date" :label="__('Fecha Inicio')" type="datetime-local" />
                    @error('eventos.' . $index . '.start_date')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror

                    <flux:input wire:model="eventos.{{ $index }}.end_date" :label="__('Fecha Fin')" type="datetime-local" />
                    @error('eventos.' . $index . '.end_date')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror

                    <flux:select wire:model="eventos.{{ $index }}.estado" :label="__('Estado')">
                        <option value="cursando">{{ __('Cursando') }}</option>
                        <option value="culminado">{{ __('Culminado') }}</option>
                    </flux:select>
                    @error('eventos.' . $index . '.estado')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror

                    <flux:button wire:click="quitarEvento({{ $index }})" variant="danger" size="sm">
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
    </x-settings.layoutevents>
</section>

