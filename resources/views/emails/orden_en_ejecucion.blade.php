<!DOCTYPE html>
<html>

<head>
    <title>Orden en Ejecución</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px;">
    <div
        style="background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #007bff; text-align: center;">¡Orden en Ejecución!</h2>
        <p>Hola, {{ $nombre }}</p>
        <p>Te informamos que la orden #{{ $id_orden }} ha sido puesta en ejecución.</p>
        <p><b>Fecha de inicio real:</b> {{ $fecha_inicio_real }}</p>
        <p style="margin-top: 30px; font-size: 14px; color: #666;">Por favor, procede con las tareas asignadas en los tiempos establecidos. ¡Mucho éxito!</p>
    </div>
</body>

</html>
