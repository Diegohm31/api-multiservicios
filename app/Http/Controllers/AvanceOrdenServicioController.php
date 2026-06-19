<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AvanceOrdenServicioService;

class AvanceOrdenServicioController extends Controller
{
    public function index()
    {
        $avances_ordenes = AvanceOrdenServicioService::getAll();
        return $this->successResponse(
            $avances_ordenes,
            $avances_ordenes->isEmpty() ? 'No se encontraron avances' : 'Avances obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_orden_servicio' => 'required|exists:ordenes_servicios,id_orden_servicio',
            'id_operativo' => 'required|exists:operativos,id_operativo',
            'descripcion' => 'required|string|max:1000',
            'porcentaje_avance' => 'required|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        $data = $request->all();

        $avance_orden = AvanceOrdenServicioService::store($data);
        if (!$avance_orden) {
            return $this->errorResponse('No se pudo crear el avance', 404);
        }
        return $this->successResponse($avance_orden, 'Avance creado correctamente');
    }

    public function show($id)
    {
        $avance_orden = AvanceOrdenServicioService::getOne($id);
        if (!$avance_orden) {
            return $this->errorResponse('Avance no encontrado', 404);
        }
        return $this->successResponse($avance_orden, 'Avance obtenido correctamente');
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_orden_servicio' => 'nullable|exists:ordenes_servicios,id_orden_servicio',
            'id_operativo' => 'nullable|exists:operativos,id_operativo',
            'descripcion' => 'nullable|string|max:1000',
            'porcentaje_avance' => 'nullable|numeric|min:0|max:100',
            'fecha_avance' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_orden_servicio') && !$request->has('id_operativo') && !$request->has('descripcion') && !$request->has('porcentaje_avance') && !$request->has('fecha_avance') && !$request->has('image')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $avance_orden = AvanceOrdenServicioService::update($id, $data);
        if (!$avance_orden) {
            return $this->errorResponse('Avance no encontrado', 404);
        }

        return $this->successResponse($avance_orden, 'Avance actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $avance_orden = AvanceOrdenServicioService::delete($id);
        if (!$avance_orden) {
            return $this->errorResponse('Avance no encontrado', 404);
        }
        return $this->successResponse($avance_orden, 'Avance eliminado correctamente');
    }
}
