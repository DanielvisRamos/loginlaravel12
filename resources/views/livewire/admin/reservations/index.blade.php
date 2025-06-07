<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\Stand;
use App\Models\Reservation;
use App\Models\Entrepreneurship;
use App\Models\Payment;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Url; // Para mantener filtros y orden en la URL

new class extends Component
{
    use WithPagination;

    // Propiedades para filtros y búsqueda
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $filterStatus = '';

    #[Url(as: 'event')]
    public string $filterEvent = '';

    // Propiedades para ordenamiento
    #[Url(as: 'sortField')]
    public string $sortField = 'created_at'; // Campo de ordenamiento por defecto
    #[Url(as: 'sortDir')]
    public string $sortDirection = 'desc';   // Dirección de ordenamiento por defecto

    // Propiedades para los selectores de filtro
    public $events = [];

    // Propiedad para el modal de detalles (similar a tu vista de usuario)
    public ?Reservation $currentDetailsReservation = null;


    // Constructor o método mount
    public function mount()
    {
        $this->loadFilterOptions();
    }

    // Carga las opciones para los filtros (ej. eventos)
    public function loadFilterOptions()
    {
        $this->events = Event::all(); // El admin puede ver todos los eventos
    }

    // Se ejecuta cuando cambian los filtros de búsqueda o selectores
    public function updated($propertyName)
    {
        // Reinicia la paginación cada vez que un filtro o búsqueda cambia
        $this->resetPage();

        // Si cambia el filtro de evento, asegúrate de que no haya un stand seleccionado
        if ($propertyName === 'filterEvent') {
            // Esto es si hubiese lógica de creación/edición de reserva de admin
        }
    }

    // Cambia el campo y la dirección de ordenamiento
    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc'; // Por defecto ascendente si cambia el campo
        }
        $this->sortField = $field;
        $this->resetPage();
    }

    /**
     * Confirma el pago de una reserva.
     * Solo se llama si la reserva y su pago están en estado 'pending'.
     *
     * @param int $reservationId El ID de la reserva a confirmar.
     * @return void
     */
    public function confirmPayment(int $reservationId)
    {
        // Buscar la reserva con sus relaciones para asegurar los estados
        $reservation = Reservation::with('payment')->find($reservationId);

        if (!$reservation) {
            session()->flash('error', 'Reserva no encontrada.');
            return;
        }

        // Validar que la reserva esté en estado 'pending' y tenga un pago 'pending'
        if (!$reservation->isPending() || !$reservation->payment || !$reservation->payment->isPending()) {
            session()->flash('error', 'No se puede confirmar el pago de esta reserva en su estado actual.');
            return;
        }

        try {
            // Iniciar una transacción para asegurar la atomicidad de las actualizaciones
            \DB::transaction(function () use ($reservation) {
                // Actualizar el estado del pago a 'completed'
                $reservation->payment->status = Payment::STATUS_COMPLETED;
                $reservation->payment->save();

                // Actualizar el estado de la reserva a 'confirmed'
                $reservation->status = Reservation::STATUS_CONFIRMED;
                $reservation->save();
            });

            session()->flash('message', 'Pago de reserva #' . $reservation->id . ' confirmado y estado actualizado a "Confirmado".');
            // No necesitamos un dispatch especial para cerrar modal aquí,
            // ya que el botón de confirmar pago no abre un modal, es una acción directa.
            $this->dispatch('reservation-updated'); // Evento para refrescar la tabla si es necesario
        } catch (\Exception $e) {
            session()->flash('error', 'Error al confirmar el pago: ' . $e->getMessage());
        }
    }

    // Propiedad computada para obtener las reservas (ahora todas, no solo las del usuario)
    public function reservations()
    {
        $query = Reservation::with(['stand.event', 'entrepreneurship', 'payment']);

        // Aplicar filtros
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('id', 'like', '%' . $this->search . '%')
                  ->orWhereHas('stand', function ($sq) {
                      $sq->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('stand.event', function ($eq) {
                      $eq->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('entrepreneurship', function ($eq) {
                      $eq->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('payment', function ($pq) {
                      $pq->where('reference_number', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterEvent) {
            $query->whereHas('stand.event', function ($eq) {
                $eq->where('id', $this->filterEvent);
            });
        }

        // Aplicar ordenamiento
        // Manejo especial para relaciones
        if (str_contains($this->sortField, '.')) {
            [$relation, $field] = explode('.', $this->sortField);
            $query->orderBy(
                \DB::table($relation)
                    ->select($field)
                    ->whereColumn("{$relation}.id", "reservations.{$relation}_id")
                    ->limit(1)
                , $this->sortDirection
            );
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }


        return $query->paginate(10); // Paginación
    }

    // Escuchar el evento 'reservation-updated' para refrescar la tabla si es necesario
    #[On('reservation-updated')]
    public function refreshReservations()
    {
        $this->resetPage();
    }
};
?>
<section class="w-full space-y-6">
    {{-- Mensajes de Sesión --}}
    @if (session('message'))
        <div class="bg-[var(--color-primary)] text-[var(--color-on-primary)] p-4 rounded-md shadow-md mb-4 text-center">
            {{ session('message') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-[var(--color-destructive)] text-[var(--color-on-destructive)] p-4 rounded-md shadow-md mb-4 text-center">
            {{ session('error') }}
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl" class="font-bold text-[var(--color-foreground)]">
            Gestión de Reservas
        </flux:heading>
    </div>

    <flux:separator />

    {{-- Filtros y Búsqueda --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <flux:input
            type="text"
            wire:model.live.debounce.300ms="search"
            :label="__('Buscar')"
            placeholder="Buscar por ID, stand, evento, emprendimiento, referencia de pago..."
            class="w-full"
        />
        <flux:select
            wire:model.live="filterStatus"
            :label="__('Filtrar por Estado')"
            class="w-full"
        >
            <option value="">Todos los Estados</option>
            <option value="reserved">Reservado</option>
            <option value="pending">Pendiente</option>
            <option value="confirmed">Confirmado</option>
            <option value="cancelled">Cancelado</option>
        </flux:select>
        <flux:select
            wire:model.live="filterEvent"
            :label="__('Filtrar por Evento')"
            class="w-full"
        >
            <option value="">Todos los Eventos</option>
            @foreach ($events as $event)
                <option value="{{ $event->id }}">{{ $event->name }}</option>
            @endforeach
        </flux:select>
    </div>

    {{-- Tabla de Reservas --}}
    <div class="overflow-x-auto">
        <table class="min-w-full bg-[var(--color-background)] rounded-lg shadow-md">
            <thead class="border-b border-[var(--color-border)]">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-[var(--color-muted-foreground)] uppercase tracking-wider">
                        <button wire:click="sortBy('id')" class="flex items-center gap-1">
                            ID
                            @if ($sortField === 'id')
                                @if ($sortDirection === 'asc')
                                    
                                @else
                                    
                                @endif
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-[var(--color-muted-foreground)] uppercase tracking-wider">
                        <button wire:click="sortBy('stand.name')" class="flex items-center gap-1">
                            Stand
                            @if ($sortField === 'stand.name')
                                @if ($sortDirection === 'asc')
                                    
                                @else
                                    
                                @endif
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-[var(--color-muted-foreground)] uppercase tracking-wider">
                        <button wire:click="sortBy('stand.event.name')" class="flex items-center gap-1">
                            Evento
                            @if ($sortField === 'stand.event.name')
                                @if ($sortDirection === 'asc')
                                    
                                @else
                                    
                                @endif
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-[var(--color-muted-foreground)] uppercase tracking-wider">
                        <button wire:click="sortBy('entrepreneurship.name')" class="flex items-center gap-1">
                            Emprendimiento
                            @if ($sortField === 'entrepreneurship.name')
                                @if ($sortDirection === 'asc')
                                    
                                @else
                                    
                                @endif
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-[var(--color-muted-foreground)] uppercase tracking-wider">
                        <button wire:click="sortBy('reservation_date')" class="flex items-center gap-1">
                            Fecha
                            @if ($sortField === 'reservation_date')
                                @if ($sortDirection === 'asc')
                                    
                                @else
                                    
                                @endif
                            @endif
                        </button>
                    </th>
                     <th class="px-6 py-3 text-left text-xs font-medium text-[var(--color-muted-foreground)] uppercase tracking-wider">
                        <button wire:click="sortBy('payment.status')" class="flex items-center gap-1">
                            Pago
                            @if ($sortField === 'payment.status')
                                @if ($sortDirection === 'asc')
                                    
                                @else
                                    
                                @endif
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-[var(--color-muted-foreground)] uppercase tracking-wider">
                        <button wire:click="sortBy('status')" class="flex items-center gap-1">
                            Reserva
                            @if ($sortField === 'status')
                                @if ($sortDirection === 'asc')
                                    
                                @else
                                    
                                @endif
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-[var(--color-muted-foreground)] uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--color-border)]">
                @forelse ($this->reservations() as $reservation)
                    <tr class="hover:bg-[var(--color-background-hover)]">
                        <td class="px-6 py-4 text-sm font-medium text-[var(--color-foreground)]">
                            {{ $reservation->id }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $reservation->stand->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $reservation->stand->event->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $reservation->entrepreneurship->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $reservation->reservation_date ? \Carbon\Carbon::parse($reservation->reservation_date)->format('d/m/Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if ($reservation->payment)
                                @if ($reservation->payment->isPending())
                                    <flux:badge variant="pill" color="yellow">Pendiente</flux:badge>
                                @elseif ($reservation->payment->isPaid())
                                    <flux:badge variant="pill" color="green">Completado</flux:badge>
                                @else
                                    <flux:badge variant="pill" color="gray">{{ $reservation->payment->status }}</flux:badge>
                                @endif
                            @else
                                <flux:badge variant="pill" color="gray">Sin Pago</flux:badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if ($reservation->isReserved())
                                <flux:badge variant="pill" color="blue">Reservado</flux:badge>
                            @elseif ($reservation->isPending())
                                <flux:badge variant="pill" color="yellow">Pendiente</flux:badge>
                            @elseif ($reservation->isConfirmed())
                                <flux:badge variant="pill" color="green">Confirmado</flux:badge>
                            @elseif ($reservation->isCancelled())
                                <flux:badge variant="pill" color="red">Cancelado</flux:badge>
                            @else
                                <flux:badge variant="pill" color="gray">{{ $reservation->status }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-right">
                            <div class="flex flex-row flex-nowrap justify-end items-center gap-2">
                                {{-- Botón de Confirmar Pago para ADMIN --}}
                                @if ($reservation->isPending() && $reservation->payment && $reservation->payment->isPending())
                                    <flux:button
                                        icon="check-circle"
                                        variant="primary"
                                        wire:click="confirmPayment({{ $reservation->id }})"
                                        wire:confirm="¿Estás seguro de que quieres confirmar el pago de esta reserva (ID: {{ $reservation->id }})?"
                                        wire:loading.attr="disabled"
                                        wire:target="confirmPayment({{ $reservation->id }})"
                                    >
                                        <span wire:loading.remove wire:target="confirmPayment({{ $reservation->id }})">Confirmar Pago</span>
                                        <span wire:loading wire:target="confirmPayment({{ $reservation->id }})">Procesando...</span>
                                    </flux:button>
                                @endif

                                {{-- Botón para abrir el modal de ver detalles --}}
                                <flux:modal.trigger name="details-reservation-admin-{{ $reservation->id }}">
                                    <flux:button icon="eye">Ver</flux:button>
                                </flux:modal.trigger>
                            </div>
                        </td>
                    </tr>

                    {{-- Modal para mostrar los detalles de la reserva (Admin) --}}
                    {{-- Este modal es similar al de usuario pero muestra más info, incluyendo el pago completo --}}
                    <flux:modal name="details-reservation-admin-{{ $reservation->id }}" class="md:w-96">
                        <form class="p-4 space-y-6">
                            <flux:heading size="lg" class="text-center font-bold">Detalles de la Reserva</flux:heading>
                            <flux:separator />

                            <div class="grid grid-cols-1 gap-4">
                                <flux:input label="ID de Reserva" :value="$reservation->id" readonly />
                                <flux:input label="Usuario (ID)" :value="$reservation->user_id" readonly /> {{-- Opcional: mostrar ID de usuario --}}
                                <flux:input label="Evento" :value="$reservation->stand->event->name ?? 'N/A'" readonly />
                                <flux:input label="Stand" :value="$reservation->stand->name ?? 'N/A'" readonly />
                                <flux:input label="Precio del Stand" :value="'$' . number_format($reservation->stand->price ?? 0, 2)" readonly />
                                <flux:input label="Emprendimiento" :value="$reservation->entrepreneurship->name ?? 'N/A'" readonly />
                                <flux:input label="Fecha de Reserva" :value="($reservation->reservation_date ? \Carbon\Carbon::parse($reservation->reservation_date)->format('d/m/Y') : 'N/A')" readonly />
                                <flux:input label="Estado de Reserva" :value="$reservation->status" readonly />

                                <flux:separator />
                                <flux:heading size="md" class="text-center">Detalles de Pago</flux:heading>

                                @if ($reservation->payment)
                                    <flux:input label="ID de Pago" :value="$reservation->payment->id" readonly />
                                    <flux:input label="Monto del Pago" :value="'$' . number_format($reservation->payment->amount, 2)" readonly />
                                    <flux:input label="Referencia de Pago" :value="$reservation->payment->reference_number" readonly />
                                    <flux:input label="Estado del Pago" :value="$reservation->payment->status" readonly />
                                    <flux:input label="Fecha de Creación de Pago" :value="$reservation->payment->created_at->format('d/m/Y H:i') ?? 'N/A'" readonly />
                                @else
                                    <flux:text class="text-center text-[var(--color-muted-foreground)]">No hay información de pago asociada.</flux:text>
                                @endif
                            </div>

                            <div class="flex justify-end mt-6">
                                <flux:button type="button" variant="ghost" @click="$dispatch('close-modal', { name: 'details-reservation-admin-{{ $reservation->id }}' })">
                                    Cerrar
                                </flux:button>
                            </div>
                        </form>
                    </flux:modal>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-sm text-[var(--color-foreground)] text-center">No hay reservas que coincidan con los filtros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-[var(--color-foreground)]">
        {{ $this->reservations()->links() }}
    </div>
</section>