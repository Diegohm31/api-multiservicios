<!DOCTYPE html>
<html>

<head>
    <title>Orden Aceptada</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px;">
    <div
        style="background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #28a745; text-align: center;">¡Orden Aceptada!</h2>
        <p>Hola, {{ $nombre }}</p>
        <p>Te informamos que la orden #{{ $id_orden }} ha sido aceptada.</p>
        @if ($observaciones)
            <p>Observaciones: {{ $observaciones }}</p>
        @endif
        <p>Se ha programado la visita de peritaje para el día: <strong>{{ \Carbon\Carbon::parse($fecha_peritaje)->format('d/m/Y h:i A') }}</strong>.</p>
        <p style="font-size: 13px; color: #555;">Si no estás de acuerdo con esta fecha o prefieres otra, puedes responder a este correo indicando la fecha de tu preferencia.</p>
        <p style="margin-top: 30px; font-size: 14px; color: #666;">Gracias por confiar en nosotros.</p>
    </div>
</body>

</html>