<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Eventos</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; font-size: 12px; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Reporte de Eventos</h2>
    <p>Fecha de generación: {{ now()->format('d/m/Y H:i') }}</p>
    <p>Total de eventos: {{ $events->count() }}</p>
    <p>Eventos activos: {{ $events->where('estado', 'cursando')->count() }}</p>
    <p>Eventos culminados: {{ $events->where('estado', 'culminado')->count() }}</p>
    <p>realizados por: {{ auth()->user()->name }} {{auth()->user()->surname}}</p>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Dirección</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($events as $event)
                <tr>
                    <td>{{ $event->name }}</td>
                    <td>{{ $event->description }}</td>
                    <td>{{ $event->address }}</td>
                    <td>{{ $event->start_date }}</td>
                    <td>{{ $event->end_date }}</td>
                    <td>{{ ucfirst($event->estado) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
