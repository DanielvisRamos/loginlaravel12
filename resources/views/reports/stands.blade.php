<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Stands</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .evento { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .totales { font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Reporte de Stands</h1>

    @foreach ($datos as $dato)
        <div class="evento">
            <h2>Evento: {{ $dato['evento'] }}</h2>
            <p>Cantidad de Stands: {{ $dato['cantidad'] }}</p>
            <p>Total Recaudado: ${{ number_format($dato['totalPrecio'], 2) }}</p>

            <table>
                <thead>
                    <tr>
                        <th>Nombre del Stand</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dato['stands'] as $stand)
                        <tr>
                            <td>{{ $stand->name }}</td>
                            <td>${{ number_format($stand->price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="totales">
        <p>Total General de Stands: {{ $totalStands }}</p>
        <p>Total General Recaudado: ${{ number_format($totalPrecioGeneral, 2) }}</p>
    </div>
</body>
</html>
