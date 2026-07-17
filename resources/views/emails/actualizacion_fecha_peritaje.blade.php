<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Actualización de Fecha de Peritaje</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <h2 style="color: #4CAF50;">Actualización de Fecha de Peritaje</h2>
        
        <p>Hola <strong>{{ $nombre }}</strong>,</p>
        
        <p>Te informamos que la fecha programada para el peritaje de tu orden <strong>#{{ $id_orden }}</strong> ha sido actualizada por nuestro equipo.</p>
        
        <div style="background-color: #f9f9f9; padding: 15px; border-left: 4px solid #4CAF50; margin: 20px 0;">
            <p style="margin: 0;"><strong>Nueva Fecha de Peritaje:</strong> {{ \Carbon\Carbon::parse($fecha_peritaje)->format('d/m/Y h:i A') }}</p>
        </div>
        
        <p>Si no estás de acuerdo con esta nueva fecha o tienes algún inconveniente, por favor responde a este correo con la fecha y hora de tu preferencia para coordinar.</p>
        
        <br>
        <p>Saludos cordiales,</p>
        <p><strong>El equipo de Multiservicios</strong></p>
    </div>
</body>
</html>
