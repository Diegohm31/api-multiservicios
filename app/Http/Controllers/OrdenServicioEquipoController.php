<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrdenServicioEquipoService;

class OrdenServicioEquipoController extends Controller
{
    public function index()
    {
        $registros = OrdenServicioEquipoService::getAll();
        return $this->successResponse(
            $registros,
            $registros->isEmpty() ? 'No se encontraron registros' : 'Registros obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_orden_servicio' => 'required|string|max:100',
            'id_equipo' => 'required|string|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);

        $data = $request->all();

        $registro = OrdenServicioEquipoService::store($data);
        if (!$registro) {
            return $this->errorResponse('No se pudo crear el registro', 404);
        }
        return $this->successResponse($registro, 'Registro creado correctamente');
    }

    public function show($id)
    {
        $registro = OrdenServicioEquipoService::getOne($id);
        if (!$registro) {
            return $this->errorResponse('Registro no encontrado', 404);
        }
        return $this->successResponse($registro, 'Registro obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_orden_servicio' => 'nullable|string|max:100',
            'id_equipo' => 'nullable|string|max:100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_orden_servicio') && !$request->has('id_equipo') && !$request->has('fecha_inicio') && !$request->has('fecha_fin')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $registro = OrdenServicioEquipoService::update($id, $data);
        if (!$registro) {
            return $this->errorResponse('Registro no encontrado', 404);
        }

        return $this->successResponse($registro, 'Registro actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $registro = OrdenServicioEquipoService::delete($id);
        if (!$registro) {
            return $this->errorResponse('Registro no encontrado', 404);
        }
        return $this->successResponse($registro, 'Registro eliminado correctamente');
    }
}
