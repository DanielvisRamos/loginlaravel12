<?php

use App\Models\Entrepreneurship;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $search = '';
    public $perPage = 5;

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function downloadReport()
    {
        $entrepreneurships = Entrepreneurship::where('estado', '!=', Entrepreneurship::ESTADO_ELIMINADO)->with('user')->get();
        $pdf = Pdf::loadView('reports.entrepreneurships', ['entrepreneurships' => $entrepreneurships]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'Emprendimientos.pdf');
    }

    public function entrepreneurships()
    {
        return Entrepreneurship::where('estado', '!=', Entrepreneurship::ESTADO_ELIMINADO)
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->with('user')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }
};

?>


<section class="w-full space-y-6">
    @include('partials/entrepreneurship-heading')
    <div class="flex justify-end p-3">
        <flux:button wire:click="downloadReport">Descargar PDF</flux:button>
    </div>

    <div>
        <flux:input type="text" wire:model.live="search" icon="magnifying-glass" placeholder="Buscar..." />
    </div>

    <x-action-message on="deleted">
        @if (session('message'))
            <span class="text-green-600">{{ session('message') }}</span>
        @endif
        @if (session('error'))
            <span class="text-red-600">{{ session('error') }}</span>
        @endif
    </x-action-message>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="border-b">
                <tr>
                    <th wire:click="sort('name')" class="cursor-pointer">Nombre
                        @if ($sortBy === 'name') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th wire:click="sort('email')" class="cursor-pointer">Email
                        @if ($sortBy === 'email') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                    </th>
                    <th>Usuario Asociado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->entrepreneurships() as $e)
                    <tr class="border-b">
                        <td class="px-6 py-4">{{ $e->name }}</td>
                        <td class="px-6 py-4">{{ $e->email }}</td>
                        <td class="px-6 py-4">{{ $e->user->name ?? 'Sin usuario' }}</td>
                        <td class="px-6 py-4 flex gap-2">
                            <flux:modal.trigger name="details-{{ $e->id }}">
                                <flux:button icon="eye">Ver</flux:button>
                            </flux:modal.trigger>
                        </td>
                    </tr>

                    <!-- Modal -->
                    <flux:modal name="details-{{ $e->id }}" class="md:w-96">
                        <div class="space-y-4">
                            <flux:heading size="lg">Detalles del Emprendimiento</flux:heading>
                            <flux:text><strong>ID:</strong> {{ $e->id }}</flux:text>
                            <flux:text><strong>Nombre:</strong> {{ $e->name }}</flux:text>
                            <flux:text><strong>Descripción:</strong> {{ $e->description }}</flux:text>
                            <flux:text><strong>Email:</strong> {{ $e->email }}</flux:text>
                            <flux:text><strong>Redes Sociales:</strong> {{ $e->social_networks }}</flux:text>
                            <flux:text><strong>Fecha de Registro:</strong> {{ $e->registration_date }}</flux:text>
                            <flux:text><strong>Usuario Asociado:</strong> {{ $e->user->name ?? 'N/A' }}</flux:text>
                            <flux:text><strong>Estado:</strong> 
                                <flux:badge variant="pill" color="lime">{{ $e->estado }}</flux:badge>
                            </flux:text>
                            @if ($e->logo_url)
                                <div class="pt-2">
                                    <img src="{{ $e->logo_url }}" alt="Logo" class="w-32 h-auto rounded shadow">
                                </div>
                            @endif
                        </div>
                    </flux:modal>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->entrepreneurships()->links() }}
    </div>
</section>

