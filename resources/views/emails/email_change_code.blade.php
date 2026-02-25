<!DOCTYPE html>
<html>

<head>
    <title>Cambio de correo electrónico</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px;">
    <div
        style="background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #0e509bff; text-align: center;">¡Cambio de correo electrónico!</h2>
        <p>Hola, {{ $nombre }}</p>
        <p>Te informamos que el correo electrónico ha sido cambiado.</p>
        <p>Nuevo correo electrónico: {{ $nuevo_correo }}</p>
        <p>Código de verificación: {{ $codigo }}</p>
        <p style="margin-top: 30px; font-size: 14px; color: #666;">Si no solicitaste este cambio, por favor ignora este
            correo.</p>
        <p style="margin-top: 30px; font-size: 14px; color: #666;">Atentamente, El equipo de Multiservicios Villarroel
        </p>
    </div>
</body>

</html>