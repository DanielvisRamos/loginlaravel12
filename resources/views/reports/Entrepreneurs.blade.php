<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Emprendedores</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            font-size: 12px;
        }

        th {
            background: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>Reporte de Eventos</h2>
    <p>Fecha de generación: {{ now()->format('d/m/Y H:i') }}</p>
    <p>Total de Emprendedores: {{ $entrepreneurs->count() }}</p>
    <p>realizado por: {{ auth()->user()->name }} {{ auth()->user()->surname }}</p>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>CI</th>
                <th>Correo</th>
                <th>Dirección</th>
                <th>Estado</th>
                <th>Fecha de Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entrepreneurs as $data)
                <tr>
                    <td>{{ $data->name }}</td>
                    <td>{{ $data->surname }}</td>
                    <td>{{ $data->Ci }}</td>
                    <td>{{ $data->email }}</td>
                    <td>{{ $data->addres }}</td>
                    <td>
                        <flux:badge variant="pill" color='lime'>{{ $data->estado }}</flux:badge>
                    </td>
                    <td>{{ $data->created_at }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>
</body>

</html>
