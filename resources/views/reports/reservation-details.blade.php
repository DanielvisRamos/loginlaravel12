<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Reserva #{{ $reservation->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        .container { width: 100%; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin-bottom: 15px; }
        .payment-info { margin-top: 20px; border-top: 1px solid #ccc; padding-top: 15px; }
        .status-message { margin-top: 30px; font-style: italic; color: gray; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Detalles de Reserva</h1>
            <p>Reserva #{{ $reservation->id }}</p>
        </div>

        <div class="details">
            <h2>Información de la Reserva</h2>
            <p><strong>Evento:</strong> {{ $reservation->stand?->event?->name ?? 'N/A' }}</p>
            <p><strong>Stand:</strong> {{ $reservation->stand?->name ?? 'N/A' }} ({{ $reservation->stand?->code ?? 'N/A' }})</p>
            <p><strong>Fecha de Reserva:</strong> {{ $reservation->reservation_date }}</p>
            <p><strong>Estado de la Reserva:</strong> {{ __(\Str::title($reservation->status)) }}</p>
        </div>

        <div class="details">
            <h2>Información del Emprendedor</h2>
            <p><strong>Nombre:</strong> {{ $reservation->user?->name ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ $reservation->user?->email ?? 'N/A' }}</p>
        </div>

        @if ($reservation->payment)
        <div class="payment-info">
            <h2>Información de Pago</h2>
            <p><strong>Método de Pago:</strong> {{ $reservation->payment->payment_method ?? 'N/A' }}</p>
            <p><strong>Referencia de Pago:</strong> {{ $reservation->payment->reference_number ?? 'N/A' }}</p>
            <p><strong>Fecha de Pago (Cargada):</strong> {{ $reservation->payment->paid_at ?? 'N/A' }}</p>
            <p><strong>Estado del Pago:</strong> {{ __(\Str::title($reservation->payment->status)) ?? 'N/A' }}</p>
        </div>
        @endif

        <div class="status-message">
            <p>Su pago está pendiente de confirmación por un administrador. Le notificaremos una vez que su pago sea verificado.</p>
        </div>
    </div>
</body>
</html>