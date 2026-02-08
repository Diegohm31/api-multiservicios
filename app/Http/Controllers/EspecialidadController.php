<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EspecialidadService;
use App\Services\OperativoEspecialidadService;

class EspecialidadController extends Controller
{
    public function index()
    {
        $especialidades = EspecialidadService::getAll();
        return $this->successResponse(
            $especialidades,
            $especialidades->isEmpty() ? 'No se encontraron especialidades' : 'Especialidades obtenidas correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'nivel' => 'required|string|max:100',
            'tarifa_hora' => 'required|numeric|min:1',
        ]);

        $data = $request->all();

        $especialidad = EspecialidadService::store($data);
        if (!$especialidad) {
            return $this->errorResponse('No se pudo crear la especialidad', 404);
        }
        return $this->successResponse($especialidad, 'Especialidad creada correctamente');
    }

    public function show($id)
    {
        $especialidad = EspecialidadService::getOne($id);
        if (!$especialidad) {
            return $this->errorResponse('Especialidad no encontrada', 404);
        }
        return $this->successResponse($especialidad, 'Especialidad obtenida correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'string|max:100',
            'nivel' => 'string|max:100',
            'tarifa_hora' => 'numeric|min:1',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('nombre') && !$request->has('nivel') && !$request->has('tarifa_hora')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $especialidad = EspecialidadService::update($id, $data);
        if (!$especialidad) {
            return $this->errorResponse('Especialidad no encontrada', 404);
        }

        return $this->successResponse($especialidad, 'Especialidad actualizada correctamente');
    }

    public function destroy(string $id)
    {
        $especialidad = EspecialidadService::delete($id);
        if (!$especialidad) {
            return $this->errorResponse('Especialidad no encontrada', 404);
        }
        return $this->successResponse($especialidad, 'Especialidad eliminada correctamente');
    }
}
