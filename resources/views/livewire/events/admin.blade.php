<?php

use App\Models\Event;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    public string $search = '';
    public int $perPage = 10;
    public $editing = [];

    use WithPagination;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getEventsProperty()
    {
        return Event::where('estado', '!=', Event::ESTADO_ELIMINADO)
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%");
            })
            ->orderBy('start_date', 'desc')
            ->paginate($this->perPage);
    }

    public function editEvent($id)
    {
        $event = Event::findOrFail($id);

        // Asignar los datos del evento al array editing
        $this->editing[$id] = [
            'name' => $event->name,
            'description' => $event->description,
            'address' => $event->address,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date,
            'estado' => $event->estado,
        ];
    }
    public function updateEvent($id)
{
    // Validación de los datos
    $this->validate([
        "editing.{$id}.name" => 'required|string|max:255',
        "editing.{$id}.description" => 'required|string',
        "editing.{$id}.address" => 'required|string',
        "editing.{$id}.start_date" => 'required|date',
        "editing.{$id}.end_date" => 'required|date|after_or_equal:editing.{$id}.start_date',
        "editing.{$id}.estado" => 'required|in:cursando,culminado',
    ]);

    // Actualización del evento
    $event = Event::findOrFail($id);
    $event->update($this->editing[$id]);

    // Limpiar los campos de edición después de actualizar
    $this->editing[$id] = [];

    // Notificar o hacer algo después de la actualización
    session()->flash('message', 'Evento actualizado exitosamente.');
}

};

?>

<section class="">
    @include('partials.events-heading')

    <x-settings.layoutevents :subheading="__('Administra los eventos del Sakura Fest')">
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
                                <flux:modal.trigger name="edit-event-{{ $event->id }}"
                                    wire:click="editEvent({{ $event->id }})">
                                    <flux:button icon="pencil">Editar</flux:button>
                                </flux:modal.trigger>

                            </td>
                        </tr>

                        <!-- Modal para editar evento -->
                        <flux:modal name="edit-event-{{ $event->id }}" class="md:w-[700px]">
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
