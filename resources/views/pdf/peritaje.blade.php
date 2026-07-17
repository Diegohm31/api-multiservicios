<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Componentes por Servicio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        h1 {
            color: #0056b3;
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .servicio-header {
            background-color: #5b9bd5;
            color: white;
            padding: 10px;
            font-weight: bold;
            margin-top: 30px;
            margin-bottom: 15px;
            border-radius: 3px;
        }
        h3 {
            color: #cc0000;
            font-size: 14px;
            text-transform: uppercase;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            padding: 8px;
            font-weight: bold;
            font-size: 12px;
        }
        td {
            text-align: center;
            padding: 8px;
            font-size: 12px;
        }
        .left-align {
            text-align: left;
            padding-left: 10px;
        }
    </style>
</head>
<body>

    <h1>Detalle de Componentes por Servicio</h1>

    @foreach($servicios as $servicio)
        <div class="servicio-header">
            Servicio: {{ $servicio->nombre ?? 'Servicio' }} (Cant: {{ $servicio->cantidad ?? 1 }})
        </div>

        @if(count($servicio->array_materiales) > 0)
            <h3>MATERIALES REQUERIDOS</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">Nombre</th>
                        <th style="width: 25%;">Unidad</th>
                        <th style="width: 25%;">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servicio->array_materiales as $material)
                        <tr>
                            <td class="left-align">{{ $material->nombre ?? 'Material' }}</td>
                            <td>{{ $material->unidad_medida ?? 'N/A' }}</td>
                            <td>{{ number_format($material->cantidad_orden_servicio_material ?? $material->cantidad, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(count($servicio->array_tipos_equipos) > 0)
            <h3>EQUIPOS REQUERIDOS</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">Nombre</th>
                        <th style="width: 25%;">Cantidad</th>
                        <th style="width: 25%;">Horas Uso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servicio->array_tipos_equipos as $equipo)
                        <tr>
                            <td class="left-align">{{ $equipo->nombre ?? 'Equipo' }}</td>
                            <td>{{ $equipo->cantidad_orden_servicio_tipo_equipo ?? $equipo->cantidad }}</td>
                            <td>{{ number_format($equipo->horas_uso, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(count($servicio->array_especialidades) > 0)
            <h3>MANO DE OBRA (ESPECIALIDADES)</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Nombre</th>
                        <th style="width: 20%;">Nivel</th>
                        <th style="width: 20%;">Cantidad</th>
                        <th style="width: 20%;">Horas Hombre</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servicio->array_especialidades as $especialidad)
                        <tr>
                            <td class="left-align">{{ $especialidad->nombre ?? 'Especialidad' }}</td>
                            <td>{{ $especialidad->nivel ?? 'N/A' }}</td>
                            <td>{{ $especialidad->cantidad_orden_servicio_especialidad ?? $especialidad->cantidad }}</td>
                            <td>{{ number_format($especialidad->horas_hombre, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

</body>
</html>
