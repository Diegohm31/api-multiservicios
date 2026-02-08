<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificacionService;

class NotificacionController extends Controller
{
    public function index()
    {
        $notificaciones = NotificacionService::getAll();
        return $this->successResponse(
            $notificaciones,
            $notificaciones->isEmpty() ? 'No se encontraron notificaciones' : 'Notificaciones obtenidas correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'string|max:100|required',
            'asunto' => 'string|max:1000|required',
            'fecha_envio' => 'date|required',
        ]);

        $data = $request->all();

        $notificacion = NotificacionService::store($data);
        if (!$notificacion) {
            return $this->errorResponse('Notificacion no creada', 404);
        }

        return $this->successResponse($notificacion, 'Notificacion creada correctamente');
    }

    public function show($id)
    {
        $notificacion = NotificacionService::getOne($id);
        if (!$notificacion) {
            return $this->errorResponse('Notificacion no encontrada', 404);
        }
        return $this->successResponse($notificacion, 'Notificacion obtenida correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'string|max:100',
            'asunto' => 'string|max:1000',
            'fecha_envio' => 'date',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_user') && !$request->has('asunto') && !$request->has('fecha_envio')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }

        $data = $request->all();

        $notificacion = NotificacionService::update($id, $data);
        if (!$notificacion) {
            return $this->errorResponse('Notificacion no encontrada', 404);
        }

        return $this->successResponse($notificacion, 'Notificacion actualizada correctamente');
    }

    public function destroy(string $id)
    {
        $notificacion = NotificacionService::delete($id);
        if (!$notificacion) {
            return $this->errorResponse('Notificacion no encontrada', 404);
        }
        return $this->successResponse($notificacion, 'Notificacion eliminada correctamente');
    }
}
