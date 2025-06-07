<?php

namespace App\Http\Livewire;

use App\Models\Event;
use App\Models\Stand;
use App\Models\Reservation;
use App\Models\Entrepreneurship;
use App\Models\Payment; // Import the Payment model
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On; // Import the On attribute for listeners

new class extends Component {
    use WithPagination;

    // Propiedades para la tabla de reservas
    public $sortBy = 'reservation_date';
    public $sortDirection = 'desc';
    public $search = '';
    public $filterStatus = '';
    public $perPage = 10;

    // Propiedades para el formulario de reserva
    public $selectedEventId;
    public $selectedStandId;
    public $selectedEntrepreneurshipId;
    public $reservationDate;

    // Propiedad para el formulario de pago
    public $paymentReference = '';

    // Propiedad para almacenar la reserva actual que se está procesando para el pago
    // No la usaremos para cargarla al abrir el modal, sino para el proceso de pago.
    public ?Reservation $currentPaymentReservation = null;

    // Propiedades para almacenar las opciones de los selectores
    public $events = [];
    public $availableStands = [];
    public $userEntrepreneurships = [];

    protected $rules = [
        'selectedEventId' => 'required|exists:events,id',
        'selectedStandId' => 'required|exists:stands,id',
        'selectedEntrepreneurshipId' => 'required|exists:entrepreneurships,id',
    ];

    protected $messages = [
        'selectedEventId.required' => 'Debes seleccionar un evento.',
        'selectedEventId.exists' => 'El evento seleccionado no es válido.',
        'selectedStandId.required' => 'Debes seleccionar un stand.',
        'selectedStandId.exists' => 'El stand seleccionado no es válido.',
        'selectedEntrepreneurshipId.required' => 'Debes seleccionar un emprendimiento.',
        'selectedEntrepreneurshipId.exists' => 'El emprendimiento seleccionado no es válido.',
        'paymentReference.required' => 'Debes ingresar un número de referencia para el pago.',
        'paymentReference.string' => 'La referencia de pago debe ser texto.',
        'paymentReference.max' => 'La referencia de pago no puede exceder los 255 caracteres.',
    ];

    public function mount()
    {
        $this->loadSelectOptions();
        $this->reservationDate = now()->toDateString();
    }

    public function loadSelectOptions()
    {
        $this->events = Event::where('status', Event::STATUS_ONGOING)->get();
        $this->userEntrepreneurships = Auth::user()->entrepreneurships()->where('status', Entrepreneurship::STATUS_ACTIVE)->get();
    }

    public function updatedSelectedEventId()
    {
        $this->availableStands = [];
        $this->selectedStandId = null;

        if ($this->selectedEventId) {
            $this->availableStands = Stand::where('event_id', $this->selectedEventId)->where('status', Stand::STATUS_AVAILABLE)->get();
        }
        $this->resetValidation('selectedStandId');
    }

    #[On('open-make-reservation-modal')]
    public function resetFormForNewReservation()
    {
        $this->reset(['selectedEventId', 'selectedStandId', 'selectedEntrepreneurshipId']);
        $this->reservationDate = now()->toDateString();
        $this->availableStands = [];
        $this->resetValidation();
        $this->loadSelectOptions();
    }

    public function createReservation()
    {
        $this->validate();

        $stand = Stand::find($this->selectedStandId);
        if (!$stand || $stand->status !== Stand::STATUS_AVAILABLE) {
            session()->flash('error', 'El stand seleccionado ya no está disponible. Por favor, elige otro.');
            $this->dispatch('close-make-reservation-modal');
            return;
        }

        try {
            $reservation = Reservation::create([
                'stand_id' => $this->selectedStandId,
                'entrepreneurship_id' => $this->selectedEntrepreneurshipId,
                'user_id' => Auth::id(),
                'reservation_date' => $this->reservationDate,
                'status' => Reservation::STATUS_RESERVED,
            ]);

            $stand->update(['status' => Stand::STATUS_RESERVED]);

            session()->flash('message', 'Reserva creada exitosamente con estado "reservado".');
            $this->dispatch('close-make-reservation-modal');
            $this->dispatch('reservation-created');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear la reserva: ' . $e->getMessage());
            $this->dispatch('close-make-reservation-modal');
        }
    }

    /**
     * Procesa el pago de una reserva.
     * Importante: $reservationId se pasa como ID.
     * Se vuelve a buscar la reserva para asegurar integridad en la acción crítica.
     *
     * @param int $reservationId El ID de la reserva a procesar.
     * @return void
     */
    public function processPayment(int $reservationId)
    {
        $this->validate([
            'paymentReference' => 'required|string|max:255',
        ]);

        // Volvemos a buscar la reserva para asegurar que es la correcta y su estado actual.
        $reservation = Reservation::find($reservationId);

        if (!$reservation) {
            session()->flash('error', 'No se encontró la reserva para procesar el pago.');
            $this->dispatch('close-modal', ['name' => 'payment-modal-' . $reservationId]);
            return;
        }

        if (!$reservation->isReserved()) {
            session()->flash('error', 'La reserva no está en estado "reservado" para procesar el pago.');
            $this->dispatch('close-modal', ['name' => 'payment-modal-' . $reservationId]);
            return;
        }

        try {
            $existingPayment = Payment::where('reservation_id', $reservation->id)->where('status', Payment::STATUS_PENDING)->first();

            if ($existingPayment) {
                session()->flash('error', 'Ya existe un pago pendiente para esta reserva. Por favor, espere la confirmación.');
                $this->dispatch('close-modal', ['name' => 'payment-modal-' . $reservationId]);
                return;
            }

            // Crear el registro de pago con estado PENDING
            Payment::create([
                'reservation_id' => $reservation->id,
                'reference_number' => $this->paymentReference,
                'amount' => $reservation->stand->price ?? 0,
                'status' => Payment::STATUS_PENDING,
            ]);

            // Actualizar el estado de la reserva a PENDING
            $reservation->status = Reservation::STATUS_PENDING;
            $reservation->save();

            session()->flash('message', 'Referencia de pago "' . $this->paymentReference . '" registrada. La reserva #' . $reservation->id . ' ahora está en estado "Pendiente" de confirmación.');
            $this->dispatch('close-modal', ['name' => 'payment-modal-' . $reservation->id]);
            $this->dispatch('reservation-updated');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al procesar el pago: ' . $e->getMessage());
            $this->dispatch('close-modal', ['name' => 'payment-modal-' . $reservationId]);
        } finally {
            $this->paymentReference = ''; // Limpiar la referencia de pago
            // No es necesario setear currentPaymentReservation a null aquí,
            // porque el modal ya no depende de esta propiedad para su visibilidad.
        }
    }

    /**
     * Función para cambiar la columna y dirección del ordenamiento.
     *
     * @param string $column Nombre de la columna por la cual se ordenará.
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
     * Obtiene las reservas del usuario autenticado con paginación, búsqueda y ordenamiento.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function reservations()
    {
        // Asegúrate de cargar las relaciones necesarias para la tabla
        // Incluye la relación 'payment' para poder mostrar el estado del pago
        $query = Reservation::where('user_id', Auth::id())->with(['stand.event', 'entrepreneurship', 'payment']);

        // Aplicar filtro de búsqueda
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->whereHas('stand', function ($sq) {
                    $sq->where('name', 'like', "%{$this->search}%");
                })->orWhereHas('entrepreneurship', function ($eq) {
                    $eq->where('name', 'like', "%{$this->search}%");
                });
            });
        }

        // Aplicar filtro por estado
        if (!empty($this->filterStatus)) {
            $query->where('status', $this->filterStatus);
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)->paginate($this->perPage);
    }
};
?>

<section class="w-full space-y-6">
    {{-- Encabezado y botón de Hacer Reserva --}}
    <div class="flex justify-between p-3 items-center">
        <h2 class="text-xl font-semibold text-[var(--color-foreground)]">Mis Reservas</h2>
        <flux:modal.trigger name="make-reservation-modal" wire:click="$dispatch('open-make-reservation-modal')">
            <flux:button>
                Hacer Reserva
            </flux:button>
        </flux:modal.trigger>
    </div>

    {{-- Área de búsqueda y filtros --}}
    <div class="flex items-center gap-4 p-3">
        <flux:input type="text" wire:model.live.debounce.300ms="search" icon="magnifying-glass"
            placeholder="{{ __('Buscar reservas por stand o emprendimiento...') }}" class="flex-1" />

        <flux:select wire:model.live="filterStatus" :label="__('Estado')" class="min-w-[180px]">
            <option value="">Todos los Estados</option>
            <option value="{{ \App\Models\Reservation::STATUS_RESERVED }}">Reservada</option>
            <option value="{{ \App\Models\Reservation::STATUS_PENDING }}">Pendiente</option>
            <option value="{{ \App\Models\Reservation::STATUS_CONFIRMED }}">Confirmada</option>
            <option value="{{ \App\Models\Reservation::STATUS_CANCELED }}">Cancelada</option>
        </flux:select>
    </div>

    {{-- Componente para mostrar mensajes de acción (éxito o error) --}}
    <x-action-message class="ml-3 inline" on="reservation-created, reservation-updated">
        @if (session('message'))
            <span class="text-[var(--color-chart-1)] dark:text-[var(--color-chart-3)]">{{ session('message') }}</span>
        @endif
        @if (session('error'))
            <span
                class="text-[var(--color-destructive)] dark:text-[var(--color-destructive)]">{{ session('error') }}</span>
        @endif
    </x-action-message>

    {{-- Tabla de Reservas --}}
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="border-b border-[var(--color-border)]">
                <tr>
                    <th wire:click="sort('id')"
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider cursor-pointer hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors">
                        <div class="flex items-center gap-1">
                            ID
                            @if ($sortBy === 'id')
                                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </div>
                    </th>
                    <th wire:click="sort('stand_id')"
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider cursor-pointer hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors">
                        <div class="flex items-center gap-1">
                            Stand
                            @if ($sortBy === 'stand_id')
                                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </div>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider">
                        Evento
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider">
                        Emprendimiento
                    </th>
                    <th wire:click="sort('reservation_date')"
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider cursor-pointer hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors">
                        <div class="flex items-center gap-1">
                            Fecha de Reserva
                            @if ($sortBy === 'reservation_date')
                                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </div>
                    </th>
                    <th wire:click="sort('status')"
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider cursor-pointer hover:bg-[var(--color-accent)] hover:text-[var(--color-accent-foreground)] transition-colors">
                        <div class="flex items-center gap-1">
                            Estado
                            @if ($sortBy === 'status')
                                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </div>
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-[var(--color-foreground)] uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->reservations() as $reservation)
                    <tr class="border-b border-[var(--color-border)]">
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">{{ $reservation->id }}</td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $reservation->stand->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $reservation->stand->event->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $reservation->entrepreneurship->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            {{ $reservation->reservation_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-[var(--color-foreground)]">
                            @php
                                $statusColor = 'gray';
                                switch ($reservation->status) {
                                    case \App\Models\Reservation::STATUS_RESERVED:
                                        $statusColor = 'blue';
                                        break;
                                    case \App\Models\Reservation::STATUS_PENDING:
                                        $statusColor = 'yellow';
                                        break; // Highlight pending
                                    case \App\Models\Reservation::STATUS_CONFIRMED:
                                        $statusColor = 'green';
                                        break;
                                    case \App\Models\Reservation::STATUS_CANCELED:
                                        $statusColor = 'red';
                                        break;
                                }
                            @endphp
                            <flux:badge variant="pill" color="{{ $statusColor }}">
                                {{ ucfirst($reservation->status) }}
                            </flux:badge>
                        </td>
                        <td class="px-6 py-4 text-sm text-right">
                            <div class="flex flex-row flex-nowrap justify-end items-center gap-2">
                                {{-- Condicional para mostrar el botón de pago --}}
                                @if ($reservation->isReserved())
                                    <flux:modal.trigger
                                        name="payment-modal-{{ $reservation->id }}"
                                        {{-- NO NECESITAMOS wire:click para setear $currentPaymentReservation,
                                           el modal ya tiene acceso a $reservation --}}
                                    >
                                        <flux:button icon="credit-card">
                                            Proceder al Pago
                                        </flux:button>
                                    </flux:modal.trigger>
                                @elseif ($reservation->isPending() && $reservation->payment && $reservation->payment->isPending())
                                     <flux:badge variant="pill" color="yellow">Pago Pendiente</flux:badge>
                                @endif
                                {{-- Botón para abrir el modal de ver detalles --}}
                                <flux:modal.trigger name="details-reservation-{{ $reservation->id }}">
                                    <flux:button icon="eye">Ver</flux:button>
                                </flux:modal.trigger>
                            </div>
                        </td>
                    </tr>

                    {{-- Modal para mostrar los detalles de la reserva --}}
                    <flux:modal name="details-reservation-{{ $reservation->id }}" class="md:w-96">
                        <div class="space-y-4 p-4">
                            <flux:heading size="lg">Detalles de la Reserva #{{ $reservation->id }}</flux:heading>

                            <flux:text>
                                **Evento:** {{ $reservation->stand->event->name ?? 'N/A' }}
                            </flux:text>
                            <flux:text>
                                **Stand:** {{ $reservation->stand->name ?? 'N/A' }}
                            </flux:text>
                            <flux:text>
                                **Emprendimiento:** {{ $reservation->entrepreneurship->name ?? 'N/A' }}
                            </flux:text>
                            <flux:text>
                                **Fecha de Reserva:** {{ $reservation->reservation_date->format('d/m/Y') }}
                            </flux:text>
                            <flux:text>
                                **Estado de Reserva:**
                                @php
                                    $statusColor = 'gray';
                                    switch ($reservation->status) {
                                        case \App\Models\Reservation::STATUS_RESERVED:
                                            $statusColor = 'blue';
                                            break;
                                        case \App\Models\Reservation::STATUS_PENDING:
                                            $statusColor = 'yellow';
                                            break;
                                        case \App\Models\Reservation::STATUS_CONFIRMED:
                                            $statusColor = 'green';
                                            break;
                                        case \App\Models\Reservation::STATUS_CANCELED:
                                            $statusColor = 'red';
                                            break;
                                    }
                                @endphp
                                <flux:badge variant="pill" color="{{ $statusColor }}">
                                    {{ ucfirst($reservation->status) }}
                                </flux:badge>
                            </flux:text>
                            <flux:text>
                                **Precio del Stand:** ${{ number_format($reservation->stand->price ?? 0, 2) }}
                            </flux:text>
                            <flux:text>
                                **Usuario que reservó:** {{ $reservation->user->name ?? 'N/A' }} (ID:
                                {{ $reservation->user_id }})
                            </flux:text>

                            {{-- Detalles de Pago si existen --}}
                            @if ($reservation->payment)
                                <hr class="border-[var(--color-border)] my-4" />
                                <flux:heading size="md">Detalles del Pago</flux:heading>
                                <flux:text>
                                    **Referencia de Pago:** {{ $reservation->payment->reference_number }}
                                </flux:text>
                                <flux:text>
                                    **Monto Pagado:** ${{ number_format($reservation->payment->amount, 2) }}
                                </flux:text>
                                <flux:text>
                                    **Estado de Pago:**
                                    @php
                                        $paymentStatusColor = 'gray';
                                        switch ($reservation->payment->status) {
                                            case \App\Models\Payment::STATUS_PENDING:
                                                $paymentStatusColor = 'yellow';
                                                break;
                                            case \App\Models\Payment::STATUS_COMPLETED:
                                                $paymentStatusColor = 'green';
                                                break;
                                            case \App\Models\Payment::STATUS_REFUNDED:
                                                $paymentStatusColor = 'red';
                                                break;
                                        }
                                    @endphp
                                    <flux:badge variant="pill" color="{{ $paymentStatusColor }}">
                                        {{ ucfirst($reservation->payment->status) }}
                                    </flux:badge>
                                </flux:text>
                                @if ($reservation->payment->paid_at)
                                    <flux:text>
                                        **Fecha de Pago:** {{ $reservation->payment->paid_at->format('d/m/Y H:i') }}
                                    </flux:text>
                                @endif
                            @endif
                        </div>
                    </flux:modal>
                    {{-- Modal para Proceder al Pago (AHORA DENTRO DEL FOREACH) --}}
                    <flux:modal name="payment-modal-{{ $reservation->id }}" class="md:w-96">
                        {{-- NO NECESITAMOS wire:loading.flex wire:target="open-payment-modal" --}}
                        {{-- Mantenemos wire:loading.flex wire:target="processPayment" para el envío del formulario --}}
                        <div wire:loading.flex wire:target="processPayment" class="absolute inset-0 bg-[var(--color-background)] bg-opacity-75 flex flex-col items-center justify-center z-50 rounded-lg">
                            <svg class="animate-spin h-8 w-8 text-[var(--color-primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="ml-3 mt-2 text-[var(--color-primary)] text-sm">Procesando pago...</span>
                        </div>

                        {{-- El formulario del modal ahora usa directamente $reservation --}}
                        <form wire:submit.prevent="processPayment({{ $reservation->id }})" class="p-4 space-y-6">
                            <flux:heading size="lg" class="text-center font-bold">Confirmar Pago de Reserva</flux:heading>
                            <flux:separator />

                            <div class="space-y-3">
                                <flux:text class="text-base text-[var(--color-foreground)]">
                                    Estás procesando el pago para la reserva **#{{ $reservation->id }}**
                                    del stand **{{ $reservation->stand->name ?? 'N/A' }}**.
                                </flux:text>
                                <flux:text class="text-xl font-bold text-[var(--color-primary)]">
                                    Monto a Pagar: **${{ number_format($reservation->stand->price ?? 0, 2) }}**
                                </flux:text>
                                <flux:text size="sm" class="text-[var(--color-muted-foreground)] leading-relaxed">
                                    Por favor, ingresa el número de referencia de tu transacción realizada para que podamos verificarla. Una vez confirmada, tu reserva pasará a estado "Confirmada".
                                </flux:text>
                            </div>

                            <div>
                                <flux:input
                                    type="text"
                                    wire:model.live="paymentReference"
                                    :label="__('Número de Referencia de Pago')"
                                    placeholder="Ej: 1234567890"
                                    required
                                    class="w-full"
                                />
                                @error('paymentReference') <span class="text-[var(--color-destructive)] text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex justify-end gap-3 mt-6">
                                <flux:button
                                    type="button"
                                    variant="ghost"
                                    {{-- Al cerrar, solo disparamos el close-modal y reseteamos la referencia --}}
                                    @click="$dispatch('close-modal', { name: 'payment-modal-{{ $reservation->id }}' }); $wire.set('paymentReference', '');"
                                >
                                    Cancelar
                                </flux:button>
                                <flux:button type="submit" icon="check" variant="primary">
                                    Confirmar Pago
                                </flux:button>
                            </div>
                        </form>
                    </flux:modal>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-sm text-[var(--color-foreground)] text-center">No
                            tienes reservas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-[var(--color-foreground)]">
        {{ $this->reservations()->links() }}
    </div>

    {{-- Modal para Hacer Reserva (formulario) --}}
    <flux:modal name="make-reservation-modal" class="md:w-[450px]">
        <form wire:submit.prevent="createReservation" class="p-4 space-y-4">
            <flux:heading size="lg">Hacer Nueva Reserva</flux:heading>

            <div>
                <flux:select wire:model.live="selectedEventId" :label="__('Selecciona el Evento')" required>
                    <option value="">-- Selecciona un Evento --</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}">{{ $event->name }}</option>
                    @endforeach
                </flux:select>
                @error('selectedEventId')
                    <span class="text-[var(--color-destructive)] text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <flux:select wire:model.live="selectedStandId" :label="__('Selecciona un Stand')" required
                    :disabled="empty($availableStands)">
                    <option value="">-- Selecciona un Stand --</option>
                    @foreach ($availableStands as $stand)
                        <option value="{{ $stand->id }}">{{ $stand->name }} ({{ $stand->size }}m² | Precio:
                            ${{ number_format($stand->price, 2) }})</option>
                    @endforeach
                </flux:select>
                @error('selectedStandId')
                    <span class="text-[var(--color-destructive)] text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <flux:select wire:model.live="selectedEntrepreneurshipId" :label="__('A nombre de mi Emprendimiento')"
                    required>
                    <option value="">-- Selecciona tu Emprendimiento --</option>
                    @foreach ($userEntrepreneurships as $entrepreneurship)
                        <option value="{{ $entrepreneurship->id }}">{{ $entrepreneurship->name }}</option>
                    @endforeach
                </flux:select>
                @error('selectedEntrepreneurshipId')
                    <span class="text-[var(--color-destructive)] text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="reservationDate" class="block text-sm font-medium text-[var(--color-foreground)] mb-1">Fecha
                    de la Reserva:</label>
                <input type="text" id="reservationDate" wire:model="reservationDate" class="flux-input" readonly
                    disabled />
                <flux:text size="sm" class="text-[var(--color-muted-foreground)] mt-1">La fecha de reserva será la
                    fecha actual.</flux:text>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <flux:button type="button" variant="ghost" @click="$dispatch('close-make-reservation-modal')">Cancelar
                </flux:button>
                <flux:button type="submit">Crear Reserva</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
