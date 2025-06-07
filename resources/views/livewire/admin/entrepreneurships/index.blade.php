<?php

use App\Models\Entrepreneurship; // Importamos el modelo Entrepreneurship
use Barryvdh\DomPDF\Facade\Pdf; // Importamos la fachada Pdf para generar PDFs
use Livewire\Volt\Component; // Importamos la clase Component de Livewire Volt
use Livewire\WithPagination; // Importamos el trait WithPagination para la paginación

new class extends Component {
    use WithPagination; // Usamos el trait WithPagination en este componente

    // Propiedades públicas para el ordenamiento, búsqueda y paginación
    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $search = '';
    public $perPage = 5;

    /**
     * Cambia la columna por la que se ordena y la dirección del ordenamiento.
     *
     * @param string $column La columna por la cual ordenar.
     * @return void
     */
    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Genera y descarga un reporte en PDF de los emprendimientos.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadReport()
    {
        // Obtiene todos los emprendimientos que no están marcados como eliminados, incluyendo la relación con el usuario.
        $entrepreneurships = Entrepreneurship::where('status', '!=', Entrepreneurship::STATUS_DELETED)
                                            ->with('user')
                                            ->get();

        // Carga la vista 'reports.entrepreneurships' y le pasa la colección de emprendimientos.
        $pdf = Pdf::loadView('reports.entrepreneurships', ['entrepreneurships' => $entrepreneurships]);

        // Devuelve una respuesta para descargar el PDF.
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'Emprendimientos.pdf');
    }

    /**
     * Obtiene los emprendimientos con paginación, filtrado por búsqueda y ordenamiento.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function entrepreneurships()
    {
        // Retorna los emprendimientos que no están eliminados,
        return Entrepreneurship::where('status', '!=', Entrepreneurship::STATUS_DELETED)
            ->where(function ($query) {
                // Aplica la búsqueda si hay un término de búsqueda.
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->with('user') // Incluye la relación con el usuario.
            ->orderBy($this->sortBy, $this->sortDirection) // Aplica el ordenamiento.
            ->paginate($this->perPage); // Aplica la paginación.
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
                                <flux:badge variant="pill" color="lime">{{ $e->status }}</flux:badge>
                            </flux:text>
                            @if ($e->logo_path)
                                <flux:text><strong>Logo:</strong></flux:text>
                                <div class="pt-2">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($e->logo_path) }}" alt="Logo" class="w-32 h-auto rounded shadow">
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