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
    // Se adapta la consulta para usar 'status' en lugar de 'estado' y las constantes
    $eventos = Event::with(['stands' => function ($query) {
        $query->where('status', Stand::STATUS_AVAILABLE);
    }])->where('status', Event::STATUS_ONGOING)->get();

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
        // Se adapta la consulta para usar 'status' en lugar de 'estado' y la constante
        $this->eventos = Event::where('status', Event::STATUS_ONGOING)->get()->toArray();
    }

    public function updatedEventoSeleccionado()
    {
        $this->cargarStands();
    }

    public function cargarStands()
    {
        $this->stands = Stand::where('event_id', $this->eventoSeleccionado)
            // Se adapta la consulta para usar 'status' en lugar de 'estado' y la constante STATUS_DELETED
            ->where('status', '!=', Stand::STATUS_DELETED)
            ->get()
            ->map(fn($stand) => [
                'id' => $stand->id,
                'name' => $stand->name,
                'price' => $stand->price,
                // Se adapta para usar 'status'
                'estado' => $stand->status,
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
                // No es necesario actualizar el estado aquí, se mantiene como está en el formulario
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
            $stand->delete(); // Eliminación lógica (usa el método delete sobrescrito en el modelo)
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
                            {{ ucfirst($stand['estado']) }} {{-- Se mantiene 'estado' para la interfaz --}}
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
</section>