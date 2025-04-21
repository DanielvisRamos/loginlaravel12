<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('events')" wire:navigate>{{ __('Crear Eventos') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Reportes de Eventos') }}</flux:navlist.item>
            <flux:navlist.item :href="route('events.admin')" wire:navigate>{{ __('Administrar Eventos') }}</flux:navlist.item>
        </flux:navlist>
    </div>
    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-5xl px-4">
            {{ $slot }}
        </div>
    </div>
</div>
