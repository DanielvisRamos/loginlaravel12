<?php

use Livewire\Volt\Component;

new class extends Component {
    public $counter = 0;

    public function increment()
    {
        $this->counter++;
    }
    public function decrement()
    {
        $this->counter--;
    }
    public function resetCounter()
    {
        $this->counter = 0;
    }
}; ?>

<div>
    @include('partials.settings-heading')
    <flux:heading size="xl" level="1">{{ __('Contador') }}</flux:heading>
    <flux:subheading size="lg" class="mb-6">{{$counter}}</flux:subheading>
    <flux:button wire:click='increment'>+</flux:button>
    <flux:button wire:click='decrement'>-</flux:button>
    <flux:button wire:click='resetCounter'>Resetear</flux:button>

</div>
