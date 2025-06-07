<?php

use App\Models\Entrepreneurship;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;

new class extends Component {
    use WithPagination, WithFileUploads;

    public $search = '';
    public $perPage = 5;

    public ?int $editingId = null;
    #[Validate('required|string|max:255')]
    public $name;
    #[Validate('required|email|max:255')]
    public $email;
    #[Validate('nullable|string')]
    public $description;
    #[Validate('nullable|string')]
    public $social_networks;
    #[Validate('nullable|image|max:2048')]
    public $logo;
    public $oldLogoPath;

    public function mount(): void
    {
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->description = '';
        $this->social_networks = '';
        $this->logo = null;
        $this->oldLogoPath = null;
        $this->resetValidation();
    }

    public function entrepreneurships()
    {
        return Entrepreneurship::where('status', '!=', Entrepreneurship::STATUS_DELETED)
            ->where('user_id', auth()->user()->id)
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->with('user')
            ->paginate($this->perPage);
    }

    public function downloadReport()
    {
        $entrepreneurships = Entrepreneurship::where('status', '!=', Entrepreneurship::STATUS_DELETED)
            ->where('user_id', auth()->user()->id)
            ->with('user')
            ->get();

        $pdf = Pdf::loadView('reports.entrepreneurships', ['entrepreneurships' => $entrepreneurships]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'Emprendimientos.pdf');
    }

    public function save()
    {
        $this->validate();

        $logoPath = $this->processLogoUpload();

        Entrepreneurship::create([
            'name' => $this->name,
            'email' => $this->email,
            'description' => $this->description,
            'social_networks' => $this->social_networks,
            'logo_path' => $logoPath,
            'user_id' => auth()->user()->id,
            'registration_date' => now(),
            'status' => Entrepreneurship::STATUS_ACTIVE,
        ]);

        session()->flash('message', 'Emprendimiento creado correctamente.');
        $this->resetForm();
    }

    public function edit(Entrepreneurship $entrepreneurship)
    {
        $this->resetForm();
        $this->editingId = $entrepreneurship->id;
        $this->name = $entrepreneurship->name;
        $this->email = $entrepreneurship->email;
        $this->description = $entrepreneurship->description;
        $this->social_networks = $entrepreneurship->social_networks ?? '';
        $this->oldLogoPath = $entrepreneurship->logo_path;
        $this->dispatch('open-modal', name: 'edit-enterprise');
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'description' => 'nullable|string',
            'social_networks' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ]);

        $entrepreneurship = Entrepreneurship::findOrFail($this->editingId);
        $newLogoPath = $this->processLogoUpload($this->oldLogoPath);

        $entrepreneurship->update([
            'name' => $this->name,
            'email' => $this->email,
            'description' => $this->description,
            'social_networks' => $this->social_networks,
            'logo_path' => $newLogoPath ?? $this->oldLogoPath,
        ]);

        session()->flash('message', 'Emprendimiento actualizado correctamente.');
        $this->resetForm();
        $this->dispatch('close-modal', name: 'edit-enterprise');
    }

    private function processLogoUpload(?string $oldLogoPath = null): ?string
    {
        if ($this->logo) {
            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->name);
            $extension = $this->logo->getClientOriginalExtension();
            $fileName = "{$safeName}_" . time() . ".{$extension}";
            $logoPath = $this->logo->storeAs('logos', $fileName, 'public');
            if ($oldLogoPath && $this->editingId) {
                Storage::disk('public')->delete($oldLogoPath);
            }
            return $logoPath;
        }
        return null;
    }

    public function delete(Entrepreneurship $entrepreneurship)
    {
        if ($entrepreneurship->logo_path) {
            Storage::disk('public')->delete($entrepreneurship->logo_path);
        }
        $entrepreneurship->delete();
        session()->flash('message', 'Emprendimiento eliminado correctamente.');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
};

?>

<section class="w-full space-y-6">
    <flux:heading size="xl">Gestión de Emprendimientos</flux:heading>

    <div class="flex justify-end p-3 gap-2">
        <flux:button wire:click="downloadReport">Descargar PDF</flux:button>
        <flux:modal.trigger name="create-enterprise">
            <flux:button>+ Nuevo Emprendimiento</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:modal name="create-enterprise" class="md:w-96">
        <div class="space-y-4">
            <flux:heading size="lg">Nuevo Emprendimiento</flux:heading>

            <flux:input label="Nombre" wire:model.live="name" />
            @error('name') <span class="text-red-600">{{ $message }}</span> @enderror

            <flux:textarea label="Descripción" wire:model.live="description" />
            @error('description') <span class="text-red-600">{{ $message }}</span> @enderror

            <flux:input type="email" label="Email" wire:model.live="email" />
            @error('email') <span class="text-red-600">{{ $message }}</span> @enderror

            <flux:input label="Redes Sociales" wire:model.live="social_networks" />
            @error('social_networks') <span class="text-red-600">{{ $message }}</span> @enderror

            <flux:input type="file" label="Logo" wire:model.live="logo" accept="image/*" />
            @error('logo') <span class="text-red-600">{{ $message }}</span> @enderror

            <div class="flex justify-end gap-2 pt-4">
                <flux:button wire:click="save" wire:loading.attr="disabled">Guardar</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="edit-enterprise" class="md:w-96" wire:key="{{ $editingId }}">
        <div class="space-y-4">
            <flux:heading size="lg">Editar Emprendimiento</flux:heading>

            <flux:input label="Nombre" wire:model.live="name" />
            @error('name') <span class="text-red-600">{{ $message }}</span> @enderror

            <flux:textarea label="Descripción" wire:model.live="description" />
            @error('description') <span class="text-red-600">{{ $message }}</span> @enderror

            <flux:input type="email" label="Email" wire:model.live="email" />
            @error('email') <span class="text-red-600">{{ $message }}</span> @enderror

            <flux:input label="Redes Sociales" wire:model.live="social_networks" />
            @error('social_networks') <span class="text-red-600">{{ $message }}</span> @enderror

            <div class="mb-2">
                <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Logo</label>
                <input type="file" wire:model.live="logo" accept="image/*" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                @error('logo') <span class="text-red-600">{{ $message }}</span> @enderror
                @if ($oldLogoPath)
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Logo actual:</p>
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($oldLogoPath) }}" alt="Logo actual" class="w-20 h-auto rounded shadow">
                    </div>
                @endif
                @if ($logo)
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nuevo logo a subir:</p>
                        <img src="{{ $logo->temporaryUrl() }}" alt="Nuevo logo" class="w-20 h-auto rounded shadow">
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <flux:button variant="primary" wire:click="$dispatch('close-modal', 'edit-enterprise')">Cancelar</flux:button>
                <flux:button wire:click="update" wire:loading.attr="disabled">Guardar Cambios</flux:button>
            </div>
        </div>
    </flux:modal>

    <div>
        <flux:input type="text" wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar..." />
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
                @forelse ($this->entrepreneurships() as $e)
                    <tr class="border-b" wire:key="{{ $e->id }}">
                        <td class="">
                            @if($e->logo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($e->logo_path) }}" alt="Logo" class="w-10 h-10 rounded-full object-cover">
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
                            <flux:modal.trigger name="edit-enterprise" wire:click="edit({{ $e }})">
                                <flux:button icon="pencil">Editar</flux:button>
                            </flux:modal.trigger>
                            <flux:button icon="trash" variant="danger" wire:click="delete({{ $e }})" wire:confirm="¿Estás seguro de que quieres eliminar este emprendimiento?">Eliminar</flux:button>
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
                            <flux:text><strong>Estado:</strong>
                                <flux:badge variant="pill" color="lime">{{ $e->status }}</flux:badge>
                            </flux:text>
                            @if ($e->logo_path)
                                <div class="pt-2">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($e->logo_path) }}" alt="Logo" class="w-32 h-auto rounded shadow">
                                </div>
                            @endif
                        </div>
                    </flux:modal>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center">No hay emprendimientos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->entrepreneurships()->links() }}
    </div>
</section>