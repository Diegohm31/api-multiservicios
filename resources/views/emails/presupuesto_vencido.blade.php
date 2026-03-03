<!DOCTYPE html>
<html>

<head>
    <title>Presupuesto Vencido</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px;">
    <div
        style="background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #dc3545; text-align: center;">¡Presupuesto Vencido!</h2>
        <p>Hola, {{ $nombre }}</p>
        <p>Te informamos que el presupuesto de la orden #{{ $id_orden }} ha vencido.</p>
        <p style="margin-top: 30px; font-size: 14px; color: #666;">Gracias por tu preferencia.</p>
    </div>
</body>

</html>