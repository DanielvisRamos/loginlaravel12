<div class="flex items-start max-md:flex-col">
    <div >
        <flux:navlist>
            
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
