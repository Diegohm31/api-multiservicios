<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        .header {
            background-color: #3b82f6;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }

        .content {
            padding: 20px;
            background-color: #f8fafc;
            border-radius: 0 0 8px 8px;
        }

        .field {
            margin-bottom: 20px;
        }

        .label {
            font-weight: bold;
            color: #64748b;
            font-size: 0.85rem;
            text-transform: uppercase;
            display: block;
            margin-bottom: 5px;
        }

        .value {
            font-size: 1.1rem;
            color: #1e293b;
            background: white;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.8rem;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Centro de Ayuda - Nueva Consulta</h2>
        </div>
        <div class="content">
            <p>Hola, <strong>{{ $admin_nombre }}</strong>. Se ha recibido una nueva consulta de un cliente que requiere
                atención:</p>

            <div class="field">
                <span class="label">Correo del Cliente:</span>
                <div class="value">{{ $cliente_email }}</div>
            </div>

            <div class="field">
                <span class="label">Duda o Consulta:</span>
                <div class="value" style="white-space: pre-wrap;">{{ $duda }}</div>
            </div>

            <p style="margin-top: 30px; font-size: 0.9rem; color: #64748b;">Por favor, responde a esta inquietud lo
                antes posible para mantener la satisfacción de nuestros usuarios.</p>
        </div>
        <div class="footer">
            Este es un mensaje automático generado por el Sistema Multiservicios App.
        </div>
    </div>
</body>

</html>