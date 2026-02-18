<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AvanceOrdenService;

class AvanceOrdenController extends Controller
{
    public function index()
    {
        $avances_ordenes = AvanceOrdenService::getAll();
        return $this->successResponse(
            $avances_ordenes,
            $avances_ordenes->isEmpty() ? 'No se encontraron avances de ordenes' : 'Avances de ordenes obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_orden' => 'required|exists:ordenes,id_orden',
            'id_operativo' => 'required|exists:operativos,id_operativo',
            'descripcion' => 'required|string|max:1000',
            'porcentaje_avance' => 'required|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->all();

        $avance_orden = AvanceOrdenService::store($data);
        if (!$avance_orden) {
            return $this->errorResponse('No se pudo crear el avance de orden', 404);
        }
        return $this->successResponse($avance_orden, 'Avance de orden creado correctamente');
    }

    public function show($id)
    {
        $avance_orden = AvanceOrdenService::getOne($id);
        if (!$avance_orden) {
            return $this->errorResponse('Avance de orden no encontrado', 404);
        }
        return $this->successResponse($avance_orden, 'Avance de orden obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_orden' => 'nullable|exists:ordenes,id_orden',
            'id_operativo' => 'nullable|exists:operativos,id_operativo',
            'descripcion' => 'nullable|string|max:1000',
            'porcentaje_avance' => 'nullable|numeric|min:0|max:100',
            'fecha_avance' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_orden') && !$request->has('id_operativo') && !$request->has('descripcion') && !$request->has('porcentaje_avance') && !$request->has('fecha_avance') && !$request->has('image')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $avance_orden = AvanceOrdenService::update($id, $data);
        if (!$avance_orden) {
            return $this->errorResponse('Avance de orden no encontrado', 404);
        }

        return $this->successResponse($avance_orden, 'Avance de orden actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $avance_orden = AvanceOrdenService::delete($id);
        if (!$avance_orden) {
            return $this->errorResponse('Avance de orden no encontrado', 404);
        }
        return $this->successResponse($avance_orden, 'Avance de orden eliminado correctamente');
    }
}
