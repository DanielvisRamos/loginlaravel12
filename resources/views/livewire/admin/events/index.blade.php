<?php

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Event;
use Carbon\Carbon;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

new class extends Component {
    public string $search = '';
    public int $perPage = 10;
    public array $editing = [];
    public $sortDirection = 'asc';
    public $sortBy = 'name';

    use WithPagination;

    protected function rules(): array
    {
        $rules = [];
        foreach ($this->editing as $id => $eventData) {
            $rules["editing.{$id}.name"] = 'required|string|max:255';
            $rules["editing.{$id}.description"] = 'required|string';
            $rules["editing.{$id}.address"] = 'required|string';
            $rules["editing.{$id}.start_date"] = 'required|date';
            $rules["editing.{$id}.end_date"] = 'required|date|after_or_equal:editing.{$id}.start_date';
        }
        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'editing.*.name' => __('Nombre'),
            'editing.*.description' => __('Descripción'),
            'editing.*.address' => __('Dirección'),
            'editing.*.start_date' => __('Fecha Inicio'),
            'editing.*.end_date' => __('Fecha Fin'),
        ];
    }

    protected function updateEventStatuses()
    {
        Event::where('status', Event::STATUS_ONGOING)
            ->where('end_date', '<', Carbon::now())
            ->update(['status' => Event::STATUS_COMPLETED]);
    }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->updateEventStatuses();
    }

    public function downloadReport()
    {
        $events = Event::where('status', '!=', Event::STATUS_DELETED)->get();
        $pdf = Pdf::loadView('reports.events', ['events' => $events]);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'eventos.pdf');
    }

    public function getEventsProperty()
    {
        $this->updateEventStatuses();
        return Event::where('status', '!=', Event::STATUS_DELETED)
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function editEvent($id)
    {
        $event = Event::findOrFail($id);
        $this->editing[$id] = [
            'name' => $event->name,
            'description' => $event->description,
            'address' => $event->address,
            'start_date' => Carbon::parse($event->start_date)->format('Y-m-d\TH:i'),
            'end_date' => Carbon::parse($event->end_date)->format('Y-m-d\TH:i'),
        ];
    }

    public function updateEvent($id)
    {
        $this->validate([
            "editing.{$id}.name" => 'required|string|max:255',
            "editing.{$id}.description" => 'required|string',
            "editing.{$id}.address" => 'required|string',
            "editing.{$id}.start_date" => 'required|date',
            "editing.{$id}.end_date" => 'required|date|after_or_equal:editing.{$id}.start_date',
        ]);

        $event = Event::findOrFail($id);
        $originalEndDate = $event->end_date;
        $originalStatus = $event->status;

        $event->update([
            'name' => $this->editing[$id]['name'],
            'description' => $this->editing[$id]['description'],
            'address' => $this->editing[$id]['address'],
            'start_date' => $this->editing[$id]['start_date'],
            'end_date' => $this->editing[$id]['end_date'],
        ]);

        $newEndDate = Carbon::parse($this->editing[$id]['end_date']);
        if ($originalStatus === Event::STATUS_COMPLETED && $newEndDate->isAfter(Carbon::now())) {
            $event->update(['status' => Event::STATUS_ONGOING]);
        } elseif ($event->status === Event::STATUS_ONGOING && $newEndDate->isBefore(Carbon::now())) {
            $event->update(['status' => Event::STATUS_COMPLETED]);
        }

        $this->updateEventStatuses();
        $this->editing[$id] = [];
        session()->flash('message', 'Evento actualizado exitosamente.');
    }

    // Métodos updated para la validación en tiempo real por campo
    public function updatedEditing($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $eventId = $parts[0];
            $field = $parts[1];
            $this->validateOnly("editing.{$eventId}.{$field}");
        }
    }
};

?>

<section class="w-full space-y-6">
    @include('partials.events-heading')
    <div class="flex justify-end mb-4">
        <flux:button wire:click="downloadReport">
            Descargar PDF
        </flux:button>
    </div>

    <div class="mb-4">
        <flux:input type="text" wire:model.live="search" icon='magnifying-glass' placeholder="{{ __('Buscar...') }}" />
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="border-b border-[var(--color-border)]">
                <tr>
                    <th wire:click="sort('name')"
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider cursor-pointer hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors">
                        <div class="flex items-center gap-1">
                            {{ __('Nombre') }}
                            @if ($sortBy === 'name')
                                @if ($sortDirection === 'asc') ↑ @else ↓ @endif
                            @endif
                        </div>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider cursor-pointer hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors">
                        {{ __('Descripción') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider cursor-pointer hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors">
                        {{ __('Dirección') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider cursor-pointer hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors">
                        {{ __('Fecha y Hora de Creación') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider">
                        {{ __('Estado') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider cursor-pointer hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors">
                        {{ __('Acciones') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->events as $event)
                    <tr class="border-b border-[var(--color-border)]">
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ $event->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ Str::limit($event->description, 50) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $event->address }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $event->created_at }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            <flux:badge variant="pill"
                                         color="{{ $event->status === \App\Models\Event::STATUS_ONGOING ? 'lime' : 'gray' }}">
                                {{ ucfirst($event->status === \App\Models\Event::STATUS_ONGOING ? 'cursando' : 'culminado') }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 text-sm text-right space-x-2">
                            <div class="flex flex-row flex-nowrap justify-end items-center gap-2">
                                <flux:modal.trigger name="edit-event-{{ $event->id }}"
                                                    wire:click="editEvent({{ $event->id }})"
                                                    wire:key="trigger-{{ $event->id }}">
                                    <flux:button icon="pencil">Editar</flux:button>
                                </flux:modal.trigger>
                            </div>
                        </td>
                    </tr>

                    <flux:modal name="edit-event-{{ $event->id }}" class="md:w-[700px]"
                                wire:key="modal-{{ $event->id }}">
                        <div class="space-y-6">
                            <flux:heading size="lg">Editar Evento</flux:heading>
                            <flux:input wire:model.live.debounce.500ms="editing.{{ $event->id }}.name"
                                        :label="__('Nombre')" />


                            <flux:textarea wire:model.live.debounce.500ms="editing.{{ $event->id }}.description"
                                           :label="__('Descripción')" />
 

                            <flux:input wire:model.live.debounce.500ms="editing.{{ $event->id }}.address"
                                        :label="__('Dirección')" />


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <flux:input type="datetime-local"
                                            wire:model.live="editing.{{ $event->id }}.start_date"
                                            :label="__('Fecha Inicio')" />


                                <flux:input type="datetime-local"
                                            wire:model.live="editing.{{ $event->id }}.end_date" :label="__('Fecha Fin')" />
                            </div>

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

    <div class="mt-4 dark:bg-transparent dark:text-white">
        {{ $this->events->links() }}
    </div>
</section>