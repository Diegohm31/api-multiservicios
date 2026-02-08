<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpcionService;

class OpcionController extends Controller
{
    public function index()
    {
        $opciones = OpcionService::getAll();
        return $this->successResponse(
            $opciones,
            $opciones->isEmpty() ? 'No se encontraron opciones' : 'Opciones obtenidas correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        $data = $request->all();

        $opcion = OpcionService::store($data);
        if (!$opcion) {
            return $this->errorResponse('No se pudo crear la opcion', 404);
        }
        return $this->successResponse($opcion, 'Opcion creada correctamente');
    }

    public function show($id)
    {
        $opcion = OpcionService::getOne($id);
        if (!$opcion) {
            return $this->errorResponse('Opcion no encontrada', 404);
        }
        return $this->successResponse($opcion, 'Opcion obtenida correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'string|max:100',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('nombre')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $opcion = OpcionService::update($id, $data);
        if (!$opcion) {
            return $this->errorResponse('Opcion no encontrada', 404);
        }

        return $this->successResponse($opcion, 'Opcion actualizada correctamente');
    }

    public function destroy(string $id)
    {
        $opcion = OpcionService::delete($id);
        if (!$opcion) {
            return $this->errorResponse('Opcion no encontrada', 404);
        }
        return $this->successResponse($opcion, 'Opcion eliminada correctamente');
    }
}
