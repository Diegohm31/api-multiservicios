<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PlanMembresiaTipoServicioService;

class PlanMembresiaTipoServicioController extends Controller
{
    public function index()
    {
        $registros = PlanMembresiaTipoServicioService::getAll();
        return $this->successResponse(
            $registros,
            $registros->isEmpty() ? 'No se encontraron registros' : 'Registros obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_plan_membresia' => 'required|string|max:100',
            'id_tipo_servicio' => 'required|string|max:100',
            'porcentaje_descuento' => 'required|numeric',
        ]);

        $data = $request->all();

        $registro = PlanMembresiaTipoServicioService::store($data);
        if (!$registro) {
            return $this->errorResponse('No se pudo crear el registro', 404);
        }
        return $this->successResponse($registro, 'Registro creado correctamente');
    }

    public function show($id)
    {
        $registro = PlanMembresiaTipoServicioService::getOne($id);
        if (!$registro) {
            return $this->errorResponse('Registro no encontrado', 404);
        }
        return $this->successResponse($registro, 'Registro obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_plan_membresia' => 'nullable|string|max:100',
            'id_tipo_servicio' => 'nullable|string|max:100',
            'porcentaje_descuento' => 'nullable|numeric',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_plan_membresia') && !$request->has('id_tipo_servicio') && !$request->has('porcentaje_descuento')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $registro = PlanMembresiaTipoServicioService::update($id, $data);
        if (!$registro) {
            return $this->errorResponse('Registro no encontrado', 404);
        }

        return $this->successResponse($registro, 'Registro actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $registro = PlanMembresiaTipoServicioService::delete($id);
        if (!$registro) {
            return $this->errorResponse('Registro no encontrado', 404);
        }
        return $this->successResponse($registro, 'Registro eliminado correctamente');
    }
}
