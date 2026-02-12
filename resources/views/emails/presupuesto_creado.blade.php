<!DOCTYPE html>
<html>

<head>
    <title>Presupuesto Creado</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px;">
    <div
        style="background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #28a745; text-align: center;">¡Presupuesto Creado!</h2>
        <p>Hola, {{ $nombre }}</p>
        <p>Te informamos que el presupuesto de la orden #{{ $id_orden }} ha sido creado.</p>
        <p>Haz clic en el siguiente botón para ver el presupuesto:</p>
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ $url }}"
                style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Ver
                Presupuesto</a>
        </div>
    </div>
</body>

</html>