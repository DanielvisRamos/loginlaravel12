<?php
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Event;
use App\Models\Stand;
use Livewire\Volt\Component;

new class extends Component
{
    public ?int $eventoSeleccionado = null;
    public array $eventos = [];
    public array $stands = [];
    public bool $isSaving = false;
    public string $message = '';


    public function exportarReportePDF(): void
{
    $eventos = Event::with(['stands' => function ($query) {
        $query->where('estado', Stand::ESTADO_DISPONIBLE);
    }])->where('estado', Event::ESTADO_CURSANDO)->get();

    $datos = [];
    $totalStands = 0;
    $totalPrecioGeneral = 0;

    foreach ($eventos as $evento) {
        $cantidadStands = $evento->stands->count();
        $totalPrecioEvento = $evento->stands->sum('price');

        $datos[] = [
            'evento' => $evento->name,
            'stands' => $evento->stands,
            'cantidad' => $cantidadStands,
            'totalPrecio' => $totalPrecioEvento,
        ];

        $totalStands += $cantidadStands;
        $totalPrecioGeneral += $totalPrecioEvento;
    }

    $pdf = Pdf::loadView('reports.stands', [
        'datos' => $datos,
        'totalStands' => $totalStands,
        'totalPrecioGeneral' => $totalPrecioGeneral,
    ])->setPaper('A4', 'portrait');

    $this->dispatch('downloadPdf', [
        'content' => base64_encode($pdf->output()),
        'filename' => 'reporte_stands.pdf',
    ]);
}

    public function mount()
    {
        $this->eventos = Event::where('estado', Event::ESTADO_CURSANDO)->get()->toArray();
    }

    public function updatedEventoSeleccionado()
    {
        $this->cargarStands();
    }

    public function cargarStands()
    {
        $this->stands = Stand::where('event_id', $this->eventoSeleccionado)
            ->where('estado', '!=', Stand::ESTADO_ELIMINADO)
            ->get()
            ->map(fn($stand) => [
                'id' => $stand->id,
                'name' => $stand->name,
                'price' => $stand->price,
                'estado' => $stand->estado,
            ])
            ->toArray();
    }

    public function actualizarStand(int $index)
    {
        if (!isset($this->stands[$index])) {
            return;
        }

        $standData = $this->stands[$index];

        $stand = Stand::find($standData['id']);

        if ($stand) {
            $stand->update([
                'name' => $standData['name'],
                'price' => $standData['price'],
            ]);
            $this->message = 'Stand actualizado exitosamente.';
        }
    }

    public function eliminarStand(int $index)
    {
        if (!isset($this->stands[$index])) {
            return;
        }

        $standData = $this->stands[$index];

        $stand = Stand::find($standData['id']);

        if ($stand) {
            $stand->delete(); // Eliminación lógica
        }

        unset($this->stands[$index]);
        $this->stands = array_values($this->stands); // Reindexar
    }

    public function guardarCambios()
    {
        $this->isSaving = true;

        foreach ($this->stands as $index => $standData) {
            $this->actualizarStand($index);
        }

        $this->isSaving = false;
        $this->message = 'Todos los cambios han sido guardados.';
    }
};
?>


<section class="w-full space-y-6">
    @include('partials.stands-heading')

    <x-settings.layoutstands 
        :heading="__('Gestión de Stands por Evento')" 
        :subheading="__('Selecciona un evento para ver y administrar sus stands')"
    >
    <flux:button variant="primary" wire:click="exportarReportePDF" class="mt-2">
    {{ __('Exportar Reporte en PDF') }}
</flux:button>
        <div class="space-y-4">
            <flux:select wire:model.live="eventoSeleccionado" :label="__('Seleccionar evento')" required>
                <option value="">-- Seleccione --</option>
                @foreach ($eventos as $evento)
                    <option value="{{ $evento['id'] }}">{{ $evento['name'] }}</option>
                @endforeach
            </flux:select>

            @if ($message)
                <div class="text-green-600 mt-2">{{ $message }}</div>
            @endif

            @if ($stands)
                @foreach ($stands as $index => $stand)
                    <div class="flex flex-col md:flex-row gap-3 items-end">
                        <flux:input 
                            wire:model="stands.{{ $index }}.name" 
                            :label="__('Nombre del Stand')" 
                            type="text" 
                        />
                        <flux:input 
                            wire:model="stands.{{ $index }}.price" 
                            :label="__('Precio')" 
                            type="number" 
                            step="0.01" 
                        />
                        <flux:text class="mt-2">
                            {{ ucfirst($stand['estado']) }}
                        </flux:text>
                        <flux:button 
                            wire:click="eliminarStand({{ $index }})" 
                            variant="danger" 
                            size="sm"
                        >
                            {{ __('Eliminar') }}
                        </flux:button>
                    </div>
                @endforeach

                <flux:button 
                    variant="primary" 
                    wire:click="guardarCambios" 
                    class="mt-4" 
                    :disabled="$isSaving"
                >
                    {{ __('Guardar Cambios') }}
                </flux:button>
            @else
                <div class="text-gray-500 mt-4">
                    {{ __('Seleccione un evento para ver sus stands.') }}
                </div>
            @endif
        </div>
    </x-settings.layoutstands>
</section>

