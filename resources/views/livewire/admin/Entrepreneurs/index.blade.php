<?php

use Barryvdh\DomPDF\Facade\Pdf; // Importamos la clase Pdf desde la librería Barryvdh\DomPDF
use App\Models\User; // Importamos el modelo User
use Livewire\Volt\Component; // Importamos la clase Component de Livewire\Volt
use Livewire\WithPagination; // Importamos el trait WithPagination de Livewire

// Definimos un nuevo componente anónimo de Livewire Volt
new class extends Component {
    use WithPagination; // Usamos el trait WithPagination para agregar funcionalidades de paginación

    public $sortBy = 'name'; // Propiedad para definir la columna por la cual se ordenará la tabla, por defecto 'name'
    public $sortDirection = 'asc'; // Propiedad para definir la dirección del ordenamiento, por defecto 'asc' (ascendente)
    public $search = ''; // Propiedad para almacenar el término de búsqueda
    public $perPage = 5; // Propiedad para definir la cantidad de registros por página, por defecto 5

    /**
     * Función para descargar un reporte en PDF de los emprendedores.
     * Este reporte incluirá solo los usuarios que no están marcados como eliminados.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadReport()
    {
        // Obtenemos todos los emprendedores que NO están eliminados y no son administradores.
        // Se mantiene el filtro de 'status' para el reporte, ya que el usuario no especificó lo contrario para el PDF.
        $entrepreneurs = User::where('status', '!=', User::STATUS_DELETED)
                             ->where('role_id', '!=', 1) // Excluye a los administradores (asumiendo role_id 1 es admin)
                             ->get();

        // Cargamos la vista `reports.entrepreneurs` pasándole los emprendedores.
        $pdf = Pdf::loadView('reports.Entrepreneurs', ['entrepreneurs' => $entrepreneurs]);

        // Retornamos el archivo PDF como descarga directa.
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream(); // Genera el contenido del PDF.
        }, 'Emprendedores.pdf'); // Nombre del archivo descargado.
    }

    /**
     * Función para cambiar la columna y dirección del ordenamiento.
     *
     * @param string $column Nombre de la columna por la cual se ordenará.
     * @return void
     */
    public function sort($column)
    {
        // Si la columna por la que se está ordenando es la misma que la columna actual
        if ($this->sortBy === $column) {
            // Invertimos la dirección del ordenamiento (ascendente a descendente o viceversa)
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Si no es la misma columna, establecemos la nueva columna y la dirección a ascendente por defecto
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Función para obtener todos los usuarios (emprendedores) con paginación, búsqueda y ordenamiento.
     * Se ha modificado para mostrar todos los estados (activo, inactivo, eliminado), excepto administradores.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function users()
    {
        // Retornamos todos los usuarios que no son administradores,
        // aplican el filtro de búsqueda en nombre o email, y los ordenamos y paginamos.
        // Se ha quitado el filtro de 'status != DELETED' para mostrar todos los estados.
        return User::where('role_id', '!=', 1) // Excluye a los administradores
            ->where(function ($query) {
                // Aplica el filtro de búsqueda por nombre o email.
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->with('role') // Cargamos la relación 'role' para mostrar el nombre del rol.
            ->orderBy($this->sortBy, $this->sortDirection) // Aplica el ordenamiento.
            ->paginate($this->perPage); // Pagina los resultados.
    }

    /**
     * Función para cambiar el estado de un usuario entre 'active' e 'inactive'.
     * Si el usuario está 'deleted', no permite activarlo/desactivarlo con este switch.
     *
     * @param int $id ID del usuario a modificar.
     * @return void
     */
    public function toggleStatus($id)
    {
        $user = User::find($id); // Busca el usuario por su ID.

        // Si el usuario no se encuentra, muestra un error.
        if (!$user) {
            session()->flash('error', __('Usuario no encontrado.'));
            return;
        }

        // Si el usuario está eliminado, no se puede cambiar su estado con este switch.
        if ($user->status === User::STATUS_DELETED) {
            session()->flash('error', __('No se puede activar/desactivar un usuario eliminado. Para activarlo, use el botón de Restaurar.'));
            return;
        }

        // Cambia el estado del usuario: si está activo, lo pone inactivo; si está inactivo, lo pone activo.
        $user->status = ($user->status === User::STATUS_ACTIVE) ? User::STATUS_INACTIVE : User::STATUS_ACTIVE;
        $user->save(); // Guarda los cambios en la base de datos.

        // Muestra un mensaje de éxito.
        session()->flash('message', __('Estado del usuario actualizado correctamente.'));
    }

    /**
     * Función para eliminar lógicamente un usuario (cambiar su estado a 'deleted').
     *
     * @param int $id ID del usuario a eliminar.
     * @return void
     */
    public function deleteUser($id)
    {
        $user = User::find($id); // Buscamos el usuario por su ID.
        // Si el usuario existe
        if ($user) {
            // Cambiamos su estado a 'deleted' (eliminación lógica) y lo guardamos.
            $user->status = User::STATUS_DELETED;
            $user->save();
            // Emitimos un evento 'deleted' para que la interfaz de usuario pueda reaccionar (ej. cerrar un modal).
            $this->dispatch('deleted');
            // Mostramos un mensaje de éxito al usuario.
            session()->flash('message', __('Usuario eliminado correctamente.'));
        } else {
            // Si el usuario no existe, mostramos un mensaje de error.
            session()->flash('error', __('Usuario no encontrado.'));
        }
    }

    /**
     * Función para restaurar un usuario eliminado (cambiar su estado de 'deleted' a 'active').
     *
     * @param int $id ID del usuario a restaurar.
     * @return void
     */
    public function restoreUser($id)
    {
        $user = User::find($id); // Busca el usuario por su ID.

        // Si el usuario no se encuentra o no está eliminado, muestra un error.
        if (!$user || $user->status !== User::STATUS_DELETED) {
            session()->flash('error', __('No se puede restaurar este usuario.'));
            return;
        }

        // Cambia el estado del usuario de 'deleted' a 'active'.
        $user->status = User::STATUS_ACTIVE;
        $user->save(); // Guarda los cambios en la base de datos.

        // Muestra un mensaje de éxito.
        session()->flash('message', __('Usuario restaurado correctamente.'));
    }
};

?>

<section class="w-full space-y-6">
    @include('partials/entrepreneurs-heading') {{-- Incluye un encabezado para la sección de emprendedores --}}

    <div class="flex justify-end p-3">
        <flux:button wire:click="downloadReport"> {{-- Botón para descargar el reporte PDF --}}
            Descargar PDF
        </flux:button>
    </div>

    <div>
        <flux:input type="text" wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Buscar...') }}" /> {{-- Campo de búsqueda con Livewire para filtrar resultados --}}
    </div>

    {{-- Componente para mostrar mensajes de acción (éxito o error) --}}
    <x-action-message class="ml-3 inline" on="deleted">
        @if (session('message'))
            <span class="text-[var(--color-chart-1)] dark:text-[var(--color-chart-3)]">{{ session('message') }}</span>
        @endif
        @if (session('error'))
            <span class="text-[var(--color-destructive)] dark:text-[var(--color-destructive)]">{{ session('error') }}</span>
        @endif
    </x-action-message>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="border-b border-[var(--color-border)]">
                <tr>
                    {{-- Encabezado de columna para Nombre, con ordenamiento --}}
                    <th wire:click="sort('name')"
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider cursor-pointer hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors">
                        <div class="flex items-center gap-1">
                            {{ __('Nombre') }}
                            @if ($sortBy === 'name') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                        </div>
                    </th>
                    {{-- Encabezado de columna para Email, con ordenamiento --}}
                    <th wire:click="sort('email')"
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider cursor-pointer hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors">
                        <div class="flex items-center gap-1">
                            {{ __('Email') }}
                            @if ($sortBy === 'email') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                        </div>
                    </th>
                    {{-- Encabezado de columna para Rol --}}
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider">
                        {{ __('Rol') }}
                    </th>
                    {{-- Columna para el Estado --}}
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider">
                        {{ __('Estado') }}
                    </th>
                    {{-- Encabezado de columna para Acciones --}}
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider">
                        {{ __('Acciones') }}
                    </th>
                </tr>
            </thead>

            <tbody>
                {{-- Itera sobre cada usuario obtenido por la función users() --}}
                @foreach ($this->users() as $user)
                    <tr class="border-b border-[var(--color-border)]">
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $user->name }} {{-- Muestra el nombre del usuario --}}
                        </td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $user->email }} {{-- Muestra el email del usuario --}}
                        </td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            <flux:badge variant="pill" icon="user"> {{ $user->role->name ?? __('Sin rol') }} </flux:badge> {{-- Muestra el rol del usuario, o 'Sin rol' si no tiene --}}
                        </td>
                        {{-- Celda para el estado del usuario --}}
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            @if ($user->status === \App\Models\User::STATUS_DELETED)
                                {{-- Si el usuario está eliminado, muestra un badge rojo --}}
                                <flux:badge variant="pill" color="red">{{ __('Eliminado') }}</flux:badge>
                            @else
                                {{-- Si el usuario no está eliminado, muestra un switch para activar/desactivar --}}
                                <flux:switch
                                    id="user-status-{{ $user->id }}" {{-- ID único para el switch --}}
                                    wire:click="toggleStatus({{ $user->id }})" {{-- Llama al método toggleStatus al hacer clic --}}
                                    :checked="$user->status === \App\Models\User::STATUS_ACTIVE" {{-- El switch estará 'checked' si el estado es activo --}}
                                >
                                    {{-- Muestra el texto 'Activo' o 'Inactivo' según el estado actual --}}
                                    {{ $user->status === \App\Models\User::STATUS_ACTIVE ? __('Activo') : __('Inactivo') }}
                                </flux:switch>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-right">
                            <div class="flex flex-row flex-nowrap justify-end items-center gap-2">
                                {{-- Botón para abrir el modal de ver datos del usuario --}}
                                <flux:modal.trigger name="edit-profile{{ $user->id }}">
                                    <flux:button icon="eye">Ver datos</flux:button>
                                </flux:modal.trigger>

                                @if ($user->status === \App\Models\User::STATUS_DELETED)
                                    {{-- Si el usuario está eliminado, muestra el botón de Restaurar --}}
                                    <flux:button icon="arrow-path" wire:click="restoreUser({{ $user->id }})" >
                                        {{ __('Restaurar') }}
                                    </flux:button>
                                @else
                                    {{-- Si el usuario no está eliminado, muestra el botón de Eliminar --}}
                                    <flux:button icon="trash" wire:click="deleteUser({{ $user->id }})" variant="danger">
                                        {{ __('Eliminar') }}
                                    </flux:button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Modal para mostrar los detalles del usuario --}}
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
                                <flux:badge variant="pill" color="lime">{{ $user->status }}</flux:badge> {{-- Muestra el estado actual del usuario --}}
                            </flux:text>
                            <flux:text>Fecha de Registro: {{ $user->created_at }}</flux:text>
                        </div>
                    </flux:modal>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-[var(--color-foreground)]">
        {{ $this->users()->links() }} {{-- Muestra los enlaces de paginación --}}
    </div>
</section>
