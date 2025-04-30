<?php

use App\Models\Phone;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public array $phones = [];
    public array $newPhones = [];
    public bool $showNewPhoneFields = false;

    public function mount(): void
    {
        $this->loadUserPhones();
    }

    protected function loadUserPhones(): void
    {
        $this->phones = Auth::user()
            ->phones()
            ->where('estado', '!=', Phone::ESTADO_ELIMINADO)
            ->get()
            ->toArray();
    }

    public function updatePhone(int $index): void
    {
        $this->validate([
            'phones.' . $index . '.phone_number' => 'required|string|max:20|min:7',
        ]);

        $phone = $this->phones[$index];

        Auth::user()->phones()
            ->where('id', $phone['id'])
            ->update(['phone_number' => $phone['phone_number']]);
    }

    public function toggleNewPhoneFields(): void
    {
        $this->showNewPhoneFields = !$this->showNewPhoneFields;
        if ($this->showNewPhoneFields && empty($this->newPhones)) {
            $this->addNewPhoneField();
        }
    }

    public function addNewPhoneField(): void
    {
        $this->newPhones[] = '+58'; // valor inicial
    }

    public function removeNewPhoneField(int $index): void
    {
        unset($this->newPhones[$index]);
        $this->newPhones = array_values($this->newPhones);
    }

    public function addPhones(): void
    {
        $this->validate([
            'newPhones.*' => 'required|string|max:20|min:7',
        ]);

        $user = Auth::user();

        foreach ($this->newPhones as $phone) {
            if (!empty(trim($phone))) {
                $user->phones()->create([
                    'phone_number' => $phone,
                    'estado' => Phone::ESTADO_ACTIVO,
                ]);
            }
        }

        $this->resetNewPhones();
        $this->loadUserPhones();
    }

    protected function resetNewPhones(): void
    {
        $this->newPhones = [];
        $this->showNewPhoneFields = false;
    }

    public function removePhone(int $index): void
    {
        $phone = $this->phones[$index];

        Auth::user()->phones()
            ->where('id', $phone['id'])
            ->update(['estado' => Phone::ESTADO_ELIMINADO]);

        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);
    }

    public function savePhones(): void
    {
        foreach ($this->phones as $index => $phone) {
            $this->updatePhone($index);
        }

        if (!empty($this->newPhones)) {
            $this->addPhones();
        }

        $this->dispatch('phones-saved'); // Emite evento para mostrar mensaje
    }
};
?>


<section class="w-full">
    <x-settings.layout :heading="__('Teléfonos')" :subheading="__('Administra los teléfonos registrados en tu cuenta')">
        <!-- Mensajes de estado -->
        <div class="space-y-3 mb-6">
            <x-action-message class="bg-trasparent text-green-700 rounded-lg" on="phones-saved">
                <div class="flex items-center gap-2"> 
                    <x-icon name="check-circle" class="w-5 h-5" />
                    <span>{{ __('Teléfonos guardados correctamente.') }}</span>
            </x-action-message>

        </div>

        <form wire:submit.prevent="savePhones" class="space-y-6">
            <!-- Sección de teléfonos existentes -->
            <div class="bg-white dark:bg-transparent rounded-lg shadow-sm p-6">
                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Teléfonos Registrados') }}
                </h3>

                <div class="space-y-4">
                    @forelse($phones as $index => $phone)
                        <div class="flex items-end gap-3">
                            <div class="flex-1">
                                <flux:input wire:model="phones.{{ $index }}.phone_number" type="tel"
                                    placeholder="Ej: +582123456789" :label="__('Teléfono').
                                    ' '.($index + 1)"
                                    class="w-full" />
                            </div>
                            <flux:button variant="danger" wire:click="removePhone({{ $index }})" type="button"
                                size="sm" title="{{ __('Eliminar teléfono') }}" class="shrink-0 p-4"
                                icon="trash">
                            </flux:button>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">
                            {{ __('No tienes teléfonos registrados') }}
                        </p>
                    @endforelse
                </div>
            </div>

            <!-- Sección para agregar nuevos teléfonos -->
            <div class="bg-white dark:bg-transparent rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Agregar Teléfonos') }}
                    </h3>

                    @unless ($showNewPhoneFields)
                        <flux:button variant="primary" wire:click="toggleNewPhoneFields" type="button" size="sm"
                            icon="plus">
                            {{ __('Agregar') }}
                        </flux:button>
                    @endunless
                </div>

                @if ($showNewPhoneFields)
                    <div class="space-y-4">
                        @foreach ($newPhones as $index => $phone)
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <flux:input wire:model="newPhones.{{ $index }}" type="tel"
                                        placeholder="Ej: +582123456789"
                                        :label="__('Nuevo teléfono').
                                        ' '.($index + 1)" class="w-full"
                                        autofocus="{{ $index === count($newPhones) - 1 ? 'true' : 'false' }}" />
                                </div>
                                <flux:button variant="danger" wire:click="removeNewPhoneField({{ $index }})"
                                    type="button" size="sm" title="{{ __('Quitar este teléfono') }}"
                                    class="shrink-0" icon="trash">
                                </flux:button>
                            </div>
                        @endforeach

                        <div class="flex flex-wrap gap-2 pt-2">
                            <flux:button variant="primary" wire:click="addNewPhoneField" type="button" size="sm">
                                {{ __('Agregar otro') }}
                            </flux:button>

                            <flux:button variant="primary" wire:click="toggleNewPhoneFields" type="button"
                                size="sm">
                                {{ __('Cancelar') }}
                            </flux:button>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Botón de guardado -->
            @if (count($phones) > 0 || $showNewPhoneFields)
                <div class="flex justify-end">
                    <flux:button variant="primary" type="submit" class="px-6 py-2">
                        {{ __('Guardar cambios') }}
                    </flux:button>
                </div>
            @endif
        </form>
    </x-settings.layout>
</section>
