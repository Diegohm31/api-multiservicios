<!DOCTYPE html>
<html>

<head>
    <title>Reporte de Pago Aceptado</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px;">
    <div
        style="background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #28a745; text-align: center;">¡Reporte de Pago Aceptado!</h2>
        <p>Hola, {{ $nombre }}</p>
        @if ($id_orden)
            <p>Te informamos que el reporte de pago de la orden #{{ $id_orden }} ha sido aceptado.</p>
        @else
            <p>Te informamos que el reporte de pago del plan de membresia #{{ $id_plan_membresia }} ha sido aceptado.</p>
        @endif
        @if ($observaciones)
            <p>Observaciones: {{ $observaciones }}</p>
        @endif
        <p style="margin-top: 30px; font-size: 14px; color: #666;">Gracias por confiar en nosotros.</p>
    </div>
</body>

</html>