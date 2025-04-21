<?php

use App\Models\Event;
use function Livewire\Volt\{state, rules, mount};
use Illuminate\Support\Facades\Auth;


state([
    'events' => [['name' => '', 'description' => '', 'address' => '', 'start_date' => '', 'end_date' => '', 'estado' => 'cursando']],
]);



rules([
    'events.*.name' => 'required|string|max:255',
    'events.*.description' => 'required|string',
    'events.*.address' => 'required|string',
    'events.*.start_date' => 'required|date',
    'events.*.end_date' => 'required|date|after:events.*.start_date',
]);

$addEvent = fn() => ($this->events[] = [
    'name' => '',
    'description' => '',
    'address' => '',
    'start_date' => '',
    'end_date' => '',
    'estado' => 'cursando',
]);

$removeEvent = fn($index) => array_splice($this->events, $index, 1);

$save = function () {
    $this->validate();

    foreach ($this->events as $eventData) {
        Event::create([
            'name' => $eventData['name'],
            'description' => $eventData['description'],
            'address' => $eventData['address'],
            'start_date' => $eventData['start_date'],
            'end_date' => $eventData['end_date'],
            'estado' => $eventData['estado'],
            'created_by' => Auth::id(),
        ]);
    }

    $this->dispatch('saved');
};

?>

<section class="">
    @include('partials.events-heading')

    <x-settings.layoutevents :subheading="__('Crea los eventos del sakura Fest aqui!')">
        <form wire:submit.prevent="save" class="space-y-6">

            @foreach ($events as $index => $event)
                <div class="p-4 border rounded-2xl shadow-sm bg-white dark:bg-transparent space-y-4" wire:key="event-{{ $index }}">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                        {{ __('Evento #:n', ['n' => $index + 1]) }}
                    </h3>

                    <!-- Nombre -->
                    <flux:input wire:model="events.{{ $index }}.name"
                                :label="__('Nombre')" type="text" />

                    <!-- Descripción -->
                    <flux:textarea wire:model="events.{{ $index }}.description"
                                   :label="__('Descripción')" />

                    <!-- Dirección -->
                    <flux:input wire:model="events.{{ $index }}.address"
                                :label="__('Dirección')" type="text" />

                    <!-- Fechas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model="events.{{ $index }}.start_date"
                                    :label="__('Fecha Inicio')" type="datetime-local" />
                        <flux:input wire:model="events.{{ $index }}.end_date"
                                    :label="__('Fecha Fin')" type="datetime-local" />
                    </div>

                    <!-- Estado -->
                    <flux:select wire:model="events.{{ $index }}.estado"
                                 :label="__('Estado')">
                        <option value="cursando">{{ __('Cursando') }}</option>
                        <option value="culminado">{{ __('Culminado') }}</option>
                    </flux:select>

                    <!-- Botón Eliminar -->
                    @if (count($events) > 1)
                        <div class="flex justify-end">
                            <flux:button  type="button"
                                         wire:click="removeEvent({{ $index }})">
                                {{ __('Eliminar') }}
                            </flux:button>
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- Botón Agregar Evento -->
            <flux:button type="button"  wire:click="addEvent">
                + {{ __('Agregar Otro Evento') }}
            </flux:button>

            <!-- Botón Guardar -->
            <div class="flex justify-end items-center">
                <flux:button variant="primary" type="submit">
                    {{ __('Guardar Eventos') }}
                </flux:button>
            
                <x-action-message class="ml-4" on="saved">
                    {{ __('Guardado.') }}
                </x-action-message>
            </div>
            
        </form>
    </x-settings.layout>
</section>

