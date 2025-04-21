<?php

// Importamos las clases necesarias
use Barryvdh\DomPDF\Facade\Pdf;         // Para generar PDF
use App\Models\Event;                   // Modelo de eventos
use Livewire\Volt\Component;           // Base para componentes Volt
use Livewire\WithPagination;           // Trait para paginación

// Declaramos un componente anónimo de Volt
new class extends Component {
    // Propiedad para el campo de búsqueda
    public string $search = '';

    // Cantidad de resultados por página
    public int $perPage = 10;

    // Arreglo para almacenar los datos editables de cada evento
    public $editing = [];

    // Usamos el trait WithPagination para paginar los resultados
    use WithPagination;

    // Método que se ejecuta cuando cambia el valor de búsqueda
    // Reinicia la página actual para evitar errores de paginación
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Método para generar el PDF con la lista de eventos
    public function downloadReport()
    {
        // Obtenemos todos los eventos que no están eliminados
        $events = Event::where('estado', '!=', Event::ESTADO_ELIMINADO)->get();

        // Cargamos la vista `reports.events` pasándole los eventos
        $pdf = Pdf::loadView('reports.events', ['events' => $events]);

        // Retornamos el archivo PDF como descarga directa
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream(); // Genera el contenido del PDF
        }, 'eventos.pdf'); // Nombre del archivo descargado
    }

    // Getter computado que obtiene los eventos paginados con filtro de búsqueda
    public function getEventsProperty()
    {
        return Event::where('estado', '!=', Event::ESTADO_ELIMINADO)
            ->where(function ($query) {
                // Aplica filtro por nombre o descripción según la búsqueda
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('description', 'like', "%{$this->search}%");
            })
            ->orderBy('start_date', 'desc') // Orden descendente por fecha de inicio
            ->paginate($this->perPage);     // Paginación
    }

    // Carga los datos de un evento para editar
    public function editEvent($id)
    {
        // Buscamos el evento por su ID o lanzamos error si no existe
        $event = Event::findOrFail($id);

        // Asignamos los valores del evento al arreglo `editing` para usar en los inputs del modal
        $this->editing[$id] = [
            'name' => $event->name,
            'description' => $event->description,
            'address' => $event->address,
            'start_date' => $event->start_date, // Fecha de inicio
            'end_date' => $event->end_date,     // Fecha de fin
            'estado' => $event->estado,         // Estado (cursando o culminado)
        ];
    }

    // Actualiza un evento con los datos del formulario
    public function updateEvent($id)
    {
        // Validamos los datos del formulario para el evento específico
        $this->validate([
            "editing.{$id}.name" => 'required|string|max:255',
            "editing.{$id}.description" => 'required|string',
            "editing.{$id}.address" => 'required|string',
            "editing.{$id}.start_date" => 'required|date',
            "editing.{$id}.end_date" => 'required|date|after_or_equal:editing.{$id}.start_date',
            "editing.{$id}.estado" => 'required|in:cursando,culminado',
        ]);

        // Buscamos el evento por ID
        $event = Event::findOrFail($id);

        // Actualizamos el evento con los datos modificados
        $event->update($this->editing[$id]);

        // Limpiamos los datos del modal de edición para ese ID
        $this->editing[$id] = [];

        // Mostramos un mensaje de confirmación al usuario
        session()->flash('message', 'Evento actualizado exitosamente.');
    }
};

?>


<section class="">
    @include('partials.events-heading')

    <x-settings.layoutevents :subheading="__('Administra los eventos del Sakura Fest')">
        <div class="flex justify-end mb-4">
            <flux:button wire:click="downloadReport">
                Descargar PDF
            </flux:button>
        </div>
        
        <!-- Buscador -->
        <div class="mb-4">
            <flux:input type="text" wire:model.live.300ms="search" placeholder="Buscar eventos..." />
        </div>

        <!-- Tabla de eventos -->
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-gray-300 dark:border-gray-600">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Nombre') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Descripción') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Dirección') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Fechas') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Estado') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('Acciones') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->events as $event)
                        <tr class="border-b border-gray-300 dark:border-gray-600">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ $event->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ Str::limit($event->description, 50) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $event->address }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $event->start_date }}<br>{{ $event->end_date }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                <flux:badge variant="pill"
                                    color="{{ $event->estado === 'cursando' ? 'lime' : 'gray' }}">
                                    {{ ucfirst($event->estado) }}
                                </flux:badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-right">
                                <!-- Botón de editar -->
                                <flux:modal.trigger name="edit-event-{{ $event->id }}"
                                    wire:click="editEvent({{ $event->id }})"
                                    wire:key="trigger-{{ $event->id }}">
                                    <flux:button icon="pencil">Editar</flux:button>
                                </flux:modal.trigger>

                            </td>
                        </tr>

                        <!-- Modal para editar evento -->
                        <flux:modal name="edit-event-{{ $event->id }}" class="md:w-[700px]"
                            wire:key="modal-{{ $event->id }}">
                            <div class="space-y-6">
                                <flux:heading size="lg">Editar Evento</flux:heading>
                                <flux:input wire:model.defer="editing.{{ $event->id }}.name"
                                    :label="__('Nombre')" />
                                <flux:textarea wire:model.defer="editing.{{ $event->id }}.description"
                                    :label="__('Descripción')" />
                                <flux:input wire:model.defer="editing.{{ $event->id }}.address"
                                    :label="__('Dirección')" />
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <flux:input type="datetime-local"
                                        wire:model.defer="editing.{{ $event->id }}.start_date"
                                        :label="__('Fecha Inicio')" />
                                    <flux:input type="datetime-local"
                                        wire:model.defer="editing.{{ $event->id }}.end_date"
                                        :label="__('Fecha Fin')" />
                                </div>
                                <flux:select wire:model.defer="editing.{{ $event->id }}.estado"
                                    :label="__('Estado')">
                                    <option value="cursando">{{ __('Cursando') }}</option>
                                    <option value="culminado">{{ __('Culminado') }}</option>
                                </flux:select>

                                <div class="flex justify-end">
                                    <flux:button variant="primary" wire:click="updateEvent({{ $event->id }})">
                                        Guardar cambios</flux:button>
                                </div>
                            </div>
                        </flux:modal>

                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No se encontraron eventos.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="mt-4 dark:bg-transparent dark:text-white">
            {{ $this->events->links() }}
        </div>

    </x-settings.layoutevents>
</section>
