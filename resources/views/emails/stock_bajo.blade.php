<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Alerta de Stock Bajo</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            width: 90%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .header {
            background: linear-gradient(135deg, #d9534f 0%, #c9302c 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 10px;
        }

        .material-list {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
        }

        .material-item {
            padding: 15px;
            border-bottom: 1px dashed #ddd;
        }

        .material-item:last-child {
            border-bottom: none;
        }

        .material-name {
            font-weight: bold;
            color: #d9534f;
            display: block;
            font-size: 1.1em;
        }

        .material-details {
            font-size: 0.9em;
            color: #666;
            display: block;
            margin-top: 4px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            font-size: 0.8em;
            text-align: center;
            color: #888;
            border-top: 1px solid #eee;
        }

        .badge {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Alerta de Inventario</h1>
        </div>
        <div class="content">
            <p>Hola, <strong>{{ $admin_nombre }}</strong>,</p>
            <p>Se ha detectado que los siguientes materiales han alcanzado o superado su nivel de stock mínimo tras la
                última operación:</p>

            <div class="material-list">
                @foreach($materiales as $material)
                    <div class="material-item">
                        <span class="material-name">{{ $material['nombre'] }}</span>
                        <span class="material-details">
                            Stock Actual: <span class="badge">{{ $material['stock_actual'] }}</span> |
                            Stock Mínimo: <strong>{{ $material['stock_minimo'] }}</strong>
                        </span>
                    </div>
                @endforeach
            </div>

            <p style="margin-top: 25px;">Se recomienda realizar la reposición de estos materiales para evitar retrasos
                en próximas órdenes.</p>
        </div>
        <div class="footer">
            <p>Este es un mensaje automático generado por el sistema API Multiservicios.</p>
        </div>
    </div>
</body>

</html>