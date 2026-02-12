<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PlanMembresiaService;

class PlanMembresiaController extends Controller
{
    public function index()
    {
        $planes_membresias = PlanMembresiaService::getAll();
        return $this->successResponse(
            $planes_membresias,
            $planes_membresias->isEmpty() ? 'No se encontraron planes de membresia' : 'Planes de membresia obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:1000',
            'descripcion' => 'required|string|max:1000',
            'duracion_meses' => 'required|numeric',
            'precio' => 'required|numeric',
            'estado' => 'required|string|max:100',
        ]);

        $data = $request->all();

        $plan_membresia = PlanMembresiaService::store($data);
        if (!$plan_membresia) {
            return $this->errorResponse('No se pudo crear el plan de membresia', 404);
        }
        return $this->successResponse($plan_membresia, 'Plan de membresia creado correctamente');
    }

    public function show($id)
    {
        $plan_membresia = PlanMembresiaService::getOne($id);
        if (!$plan_membresia) {
            return $this->errorResponse('Plan de membresia no encontrado', 404);
        }
        return $this->successResponse($plan_membresia, 'Plan de membresia obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'nullable|string|max:1000',
            'descripcion' => 'nullable|string|max:1000',
            'duracion_meses' => 'nullable|numeric',
            'precio' => 'nullable|numeric',
            'estado' => 'nullable|string|max:100',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('nombre') && !$request->has('descripcion') && !$request->has('duracion_meses') && !$request->has('precio') && !$request->has('estado')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $plan_membresia = PlanMembresiaService::update($id, $data);
        if (!$plan_membresia) {
            return $this->errorResponse('Plan de membresia no encontrado', 404);
        }

        return $this->successResponse($plan_membresia, 'Plan de membresia actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $plan_membresia = PlanMembresiaService::delete($id);
        if (!$plan_membresia) {
            return $this->errorResponse('Plan de membresia no encontrado', 404);
        }
        return $this->successResponse($plan_membresia, 'Plan de membresia eliminado correctamente');
    }
}
