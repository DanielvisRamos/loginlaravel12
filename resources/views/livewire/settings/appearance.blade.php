<?php

use Livewire\Volt\Component;

// Este componente Livewire (Volt) gestiona la configuración de apariencia del usuario.
new class extends Component {
    // No se define ninguna lógica PHP específica en este componente por el momento.
    // La interacción de cambio de apariencia probablemente se maneja a través de JavaScript
    // y el binding de datos de Alpine.js ($flux.appearance).
}; ?>

<section class="w-full">
    {{-- Incluye el encabezado de la sección de configuración (título y subtítulo). --}}
    @include('partials.settings-heading')

    {{-- Utiliza un layout específico para las secciones de configuración. --}}
    <x-settings.layout :heading="__('Apariencia')" :subheading="__('Actualiza la configuración de apariencia de tu cuenta')">
        {{-- Grupo de radio botones segmentados para seleccionar el tema de apariencia. --}}
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            {{-- Opción para el tema claro. --}}
            <flux:radio value="light" icon="sun">{{ __('Claro') }}</flux:radio>
            {{-- Opción para el tema oscuro. --}}
            <flux:radio value="dark" icon="moon">{{ __('Oscuro') }}</flux:radio>
            {{-- Opción para usar la configuración de apariencia del sistema operativo. --}}
            <flux:radio value="system" icon="computer-desktop">{{ __('Sistema') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>