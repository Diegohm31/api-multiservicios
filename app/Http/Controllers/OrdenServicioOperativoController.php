<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrdenServicioOperativoService;

class OrdenServicioOperativoController extends Controller
{
    public function index()
    {
        $registros = OrdenServicioOperativoService::getAll();
        return $this->successResponse(
            $registros,
            $registros->isEmpty() ? 'No se encontraron registros' : 'Registros obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_orden_servicio' => 'required|string|max:100',
            'id_operativo' => 'required|string|max:100',
            'id_especialidad' => 'required|string|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'es_jefe' => 'required|boolean',
        ]);

        $data = $request->all();

        $registro = OrdenServicioOperativoService::store($data);
        if (!$registro) {
            return $this->errorResponse('No se pudo crear el registro', 404);
        }
        return $this->successResponse($registro, 'Registro creado correctamente');
    }

    public function show($id)
    {
        $registro = OrdenServicioOperativoService::getOne($id);
        if (!$registro) {
            return $this->errorResponse('Registro no encontrado', 404);
        }
        return $this->successResponse($registro, 'Registro obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_orden_servicio' => 'nullable|string|max:100',
            'id_operativo' => 'nullable|string|max:100',
            'id_especialidad' => 'nullable|string|max:100',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'es_jefe' => 'nullable|boolean',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_orden_servicio') && !$request->has('id_operativo') && !$request->has('id_especialidad') && !$request->has('fecha_inicio') && !$request->has('fecha_fin') && !$request->has('es_jefe')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $registro = OrdenServicioOperativoService::update($id, $data);
        if (!$registro) {
            return $this->errorResponse('Registro no encontrado', 404);
        }

        return $this->successResponse($registro, 'Registro actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $registro = OrdenServicioOperativoService::delete($id);
        if (!$registro) {
            return $this->errorResponse('Registro no encontrado', 404);
        }
        return $this->successResponse($registro, 'Registro eliminado correctamente');
    }
}
