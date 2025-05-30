<?php

use App\Models\Entrepreneurship;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

// Componente anónimo de Livewire Volt para gestionar emprendimientos
new class extends Component {
    use WithPagination, WithFileUploads;

    // Propiedades públicas para el formulario y la búsqueda
    public $search = '';
    public $perPage = 5;
    public $name;
    public $email;
    public $description;
    public $social_networks;
    public $logo;

    // Método para obtener los emprendimientos paginados y filtrados por búsqueda
    public function entrepreneurships()
    {
        return Entrepreneurship::where('estado', '!=', Entrepreneurship::ESTADO_ELIMINADO)
            ->where('user_id', auth()->user()->id)
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->with('user')
            ->paginate($this->perPage);
    }

    // Método para descargar un reporte PDF de los emprendimientos
    public function downloadReport()
    {
        $entrepreneurships = Entrepreneurship::where('estado', '!=', Entrepreneurship::ESTADO_ELIMINADO)
            ->where('user_id', auth()->user()->id)
            ->with('user')
            ->get();

        $pdf = Pdf::loadView('reports.entrepreneurships', ['entrepreneurships' => $entrepreneurships]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'Emprendimientos.pdf');
    }

    // Método para limpiar el formulario y validaciones
    public function resetForm()
    {
        $this->reset(['name', 'email', 'description', 'social_networks', 'logo']);
        $this->resetValidation();
    }

    // Método para guardar un nuevo emprendimiento
    public function save()
    {
        // Validación de los campos del formulario
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'description' => 'nullable|string',
            'social_networks' => 'nullable|string',
            'logo' => 'required|image|max:2048',
        ]);

        // Sanitiza el nombre para usarlo como nombre de archivo
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->name);
        $extension = $this->logo->getClientOriginalExtension();
        $fileName = "{$safeName}.{$extension}";

        // Guarda el archivo en public/storage/logos con el nombre del emprendimiento
        $logoPath = $this->logo->storeAs('logos', $fileName, 'public');
        $logoUrl = \Illuminate\Support\Facades\Storage::url($logoPath);

        // Crea el registro del emprendimiento en la base de datos
        Entrepreneurship::create([
            'name' => $this->name,
            'email' => $this->email,
            'description' => $this->description,
            'social_networks' => json_encode($this->social_networks),
            'logo_path' => $logoUrl,
            'user_id' => auth()->user()->id,
            'registration_date' => now(),
        ]);

        // Mensaje de éxito y reseteo del formulario
        session()->flash('message', 'Emprendimiento creado correctamente.');
        $this->resetForm();
    }
};

?>


<section class="w-full space-y-6">
    <flux:heading size="xl">Gestión de Emprendimientos</flux:heading>

    <div class="flex justify-end p-3">
        <flux:modal.trigger name="create-enterprise">
            <flux:button>+ Nuevo Emprendimiento</flux:button>
        </flux:modal.trigger>
    </div>
    <flux:modal name="create-enterprise" class="md:w-96">
        <div class="space-y-4">
            <flux:heading size="lg">Nuevo Emprendimiento</flux:heading>

            <flux:input label="Name" wire:model.defer="name" />
            @error('name')
                <span class="text-red-600">{{ $message }}</span>
            @enderror

            <flux:textarea label="Descripción" wire:model.defer="description" />

            <flux:input type="email" label="Email" wire:model.defer="email" />
            @error('email')
                <span class="text-red-600">{{ $message }}</span>
            @enderror

            <flux:input label="Redes Sociales" wire:model.defer="social_networks" />
            @error('social_networks')
                <span class="text-red-600">{{ $message }}</span>
            @enderror

            <flux:input type="file" label="Logo" wire:model="logo" accept="image/*" />
            @error('logo')
                <span class="text-red-600">{{ $message }}</span>
            @enderror

            <div class="flex justify-end gap-2 pt-4">
                <flux:button wire:click="save">Guardar</flux:button>
            </div>
        </div>
    </flux:modal>

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
                <th>Logo</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($this->entrepreneurships() as $e)
                <tr class="border-b">
                    <td class="">
                        @if($e->logo_path)
                            <img src="{{ $e->logo_path }}" alt="Logo" class="w-10 h-10 rounded-full object-cover">
                        @else
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                <span class="text-xs text-gray-500">Sin logo</span>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">{{ $e->name }}-----><span class="text-xs text-gray-500">{{ $e->description }}</span> </td>
                    <td class="px-6 py-4 flex gap-2">
                        <flux:modal.trigger name="details-{{ $e->id }}">
                            <flux:button icon="eye">Ver</flux:button>
                        </flux:modal.trigger>
                    </td>
                </tr>

                <!-- Modal (se mantiene igual) -->
                <flux:modal name="details-{{ $e->id }}" class="md:w-96">
                    <div class="space-y-4">
                        <flux:heading size="lg">Detalles del Emprendimiento</flux:heading>
                        <flux:text><strong>ID:</strong> {{ $e->id }}</flux:text>
                        <flux:text><strong>Name:</strong> {{ $e->name }}</flux:text>
                        <flux:text><strong>Descripción:</strong> {{ $e->description }}</flux:text>
                        <flux:text><strong>Email:</strong> {{ $e->email }}</flux:text>
                        <flux:text><strong>Redes Sociales:</strong> {{ $e->social_networks }}</flux:text>
                        <flux:text><strong>Fecha de Registro:</strong> {{ $e->registration_date }}</flux:text>
                        <flux:text><strong>Estado:</strong>
                            <flux:badge variant="pill" color="lime">{{ $e->estado }}</flux:badge>
                        </flux:text>
                        @if ($e->logo_path)
                            <div class="pt-2">
                                <img src="{{ $e->logo_path }}" alt="Logo" class="w-32 h-auto rounded shadow">
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
