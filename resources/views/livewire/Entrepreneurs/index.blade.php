<?php
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $search = '';
    public $perPage = 5;

    public function downloadReport()
    {
        // Obtenemos todos los eventos que no están eliminados
        $entrepreneurs = User::where('estado', '!=', User::ESTADO_ELIMINADO)->where('role_id', '!=', 1)->get();

        // Cargamos la vista `reports.events` pasándole los eventos
        $pdf = Pdf::loadView('reports.Entrepreneurs', ['entrepreneurs' => $entrepreneurs]);

        // Retornamos el archivo PDF como descarga directa
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream(); // Genera el contenido del PDF
        }, 'Emprendedores.pdf'); // Nombre del archivo descargado
    }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function users()
    {
        //emprendedores
        return User::where('estado', '!=', User::ESTADO_ELIMINADO)
            ->where('role_id', '!=', 1)
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%");
            })
            ->with('role')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->estado = User::ESTADO_ELIMINADO;
            $user->save();
            $this->dispatch('deleted'); // Emitir evento 'deleted'
            session()->flash('message', __('Usuario eliminado correctamente.'));
        } else {
            session()->flash('error', __('Usuario no encontrado.'));
        }
    }
};
?>

<section class="w-full space-y-6">
    @include('partials/entrepreneurs-heading')
    <x-settings.layoutentrepreneurs>
    <div class="flex justify-end mb-4">
        <flux:button wire:click="downloadReport">
            Descargar PDF
        </flux:button>
    </div>
    <!-- Buscador -->
    <div class="mb-4">
        <flux:input type="text" wire:model.live="search" icon='magnifying-glass' placeholder="{{ __('Buscar...') }}" />
    </div>

    <!-- Tabla con solo bordes inferiores -->
    <div class="overflow-x-auto">
        <x-action-message class="ml-3 inline" on="deleted">
            @if (session('message'))
                <span class="text-green-600 dark:text-green-400">{{ session('message') }}</span>
            @endif
            @if (session('error'))
                <span class="text-red-600 dark:text-red-400">{{ session('error') }}</span>
            @endif
        </x-action-message>
        <div class="overflow-x-auto"></div>
        <table class="min-w-full">
            <!-- Cabecera con fondo gris y solo borde inferior -->
            <thead class="border-b border-gray-300 dark:border-gray-600  ">
                <tr>
                    <th wire:click="sort('name')"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex items-center gap-1">
                            {{ __('Nombre') }}
                            @if ($sortBy === 'name')
                                @if ($sortDirection === 'asc')
                                    ↑
                                @else
                                    ↓
                                @endif
                            @endif
                        </div>
                    </th>
                    <th wire:click="sort('email')"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        <div class="flex items-center gap-1">
                            {{ __('Email') }}
                            @if ($sortBy === 'email')
                                @if ($sortDirection === 'asc')
                                    ↑
                                @else
                                    ↓
                                @endif
                            @endif
                        </div>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ __('Rol') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ __('Acciones') }}
                    </th>
                </tr>
            </thead>

            <!-- Filas con solo borde inferior -->
            <tbody>
                @foreach ($this->users() as $user)
                    <tr class="border-b border-gray-300 dark:border-gray-600">
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ $user->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                            <span
                                class="px-2 py-1 text-xs rounded-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                <flux:badge variant="pill" icon="user">{{ $user->role->name ?? __('Sin rol') }}
                                </flux:badge>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-right">
                            <flux:modal.trigger name="edit-profile{{ $user->name }}">
                                <flux:button icon='eye'>Ver datos</flux:button>
                            </flux:modal.trigger>
                            <flux:button icon='trash' wire:click='deleteUser({{ $user->id }})'>Eliminar
                            </flux:button>
                        </td>
                    </tr>
                    <flux:modal name="edit-profile{{ $user->name }}" class="md:w-96">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">Informacion de Emprendedor</flux:heading>
                            </div>

                            <flux:text class="mt-2">Id de usuario: {{ $user->id }}</flux:text>
                            <flux:text class="mt-2">Nombre: {{ $user->name }}</flux:text>
                            <flux:text class="mt-2">Apellido: {{ $user->surname }}</flux:text>
                            <flux:text class="mt-2">Cedula de Identidad: {{ $user->CI }}</flux:text>
                            <flux:text class="mt-2">Correo: {{ $user->email }}</flux:text>
                            <flux:text class="mt-2">Direccion: {{ $user->address }}</flux:text>
                            <flux:text class="mt-2">Estado: <flux:badge variant="pill" color='lime'>
                                    {{ $user->estado }}</flux:badge>
                            </flux:text>
                            <flux:text class="mt-2">Fecha de Registro: {{ $user->created_at }}</flux:text>
                        </div>
                    </flux:modal>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4 text-gray-700 dark:text-gray-300">
        {{ $this->users()->links() }}
    </div>
    </x-settings.layoutentrepreneurs>



</section>
