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
    <!-- Botón de descarga -->
    <div class="flex justify-end p-3">
        <flux:button wire:click="downloadReport">
            Descargar PDF
        </flux:button>
    </div>

    <!-- Buscador -->
    <div>
        <flux:input type="text" wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Buscar...') }}" />
    </div>

    <!-- Mensajes de acción -->
    <x-action-message class="ml-3 inline" on="deleted">
        @if (session('message'))
            <span class="text-[var(--color-chart-1)] dark:text-[var(--color-chart-3)]">{{ session('message') }}</span>
        @endif
        @if (session('error'))
            <span
                class="text-[var(--color-destructive)] dark:text-[var(--color-destructive)]">{{ session('error') }}</span>
        @endif
    </x-action-message>

    <!-- Tabla de usuarios -->
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <!-- Cabecera -->
            <thead class="border-b border-[var(--color-border)]">
                <tr>
                    <th wire:click="sort('name')"
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider cursor-pointer hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors">
                        <div class="flex items-center gap-1">
                            {{ __('Nombre') }}
                            @if ($sortBy === 'name')
                                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </div>
                    </th>
                    <th wire:click="sort('email')"
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider cursor-pointer hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors">
                        <div class="flex items-center gap-1">
                            {{ __('Email') }}
                            @if ($sortBy === 'email')
                                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </div>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider">
                        {{ __('Rol') }}
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider">
                        {{ __('Acciones') }}
                    </th>
                </tr>
            </thead>

            <!-- Cuerpo -->
            <tbody>
                @foreach ($this->users() as $user)
                    <tr class="border-b border-[var(--color-border)]">
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $user->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            <flux:badge variant="pill" icon="user">
                                {{ $user->role->name ?? __('Sin rol') }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 text-sm text-right">
                            <div class="flex flex-row flex-nowrap justify-end items-center gap-2">
                                <flux:modal.trigger name="edit-profile{{ $user->id }}">
                                    <flux:button icon="eye">Ver datos</flux:button>
                                </flux:modal.trigger>
                                <flux:button icon="trash" wire:click="deleteUser({{ $user->id }})">
                                    Eliminar
                                </flux:button>
                            </div>
                        </td>

                    </tr>

                    <!-- Modal de detalles -->
                    <flux:modal name="edit-profile{{ $user->id }}" class="md:w-96">
                        <div class="space-y-4">
                            <flux:heading size="lg">Información del Emprendedor</flux:heading>

                            <flux:text>Id de usuario: {{ $user->id }}</flux:text>
                            <flux:text>Nombre: {{ $user->name }}</flux:text>
                            <flux:text>Apellido: {{ $user->surname }}</flux:text>
                            <flux:text>Cédula de Identidad: {{ $user->CI }}</flux:text>
                            <flux:text>Correo: {{ $user->email }}</flux:text>
                            <flux:text>Dirección: {{ $user->address }}</flux:text>
                            <flux:text>Estado:
                                <flux:badge variant="pill" color="lime">{{ $user->estado }}</flux:badge>
                            </flux:text>
                            <flux:text>Fecha de Registro: {{ $user->created_at }}</flux:text>
                        </div>
                    </flux:modal>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4 text-[var(--color-foreground)]">
        {{ $this->users()->links() }}
    </div>
</section>
