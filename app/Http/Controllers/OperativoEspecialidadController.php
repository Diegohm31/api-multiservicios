<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OperativoEspecialidadService;

class OperativoEspecialidadController extends Controller
{
    public function index()
    {
        $registros = OperativoEspecialidadService::getAll();
        return $this->successResponse(
            $registros,
            $registros->isEmpty() ? 'No se encontraron registros' : 'Registros obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_operativo' => 'required|string|max:100',
            'id_especialidad' => 'required|string|max:100',
        ]);

        $data = $request->all();

        $registro = OperativoEspecialidadService::store($data);
        if (!$registro) {
            return $this->errorResponse('No se pudo crear el registro', 404);
        }
        return $this->successResponse($registro, 'Registro creado correctamente');
    }

    public function show($id)
    {
        $registro = OperativoEspecialidadService::getOne($id);
        if (!$registro) {
            return $this->errorResponse('Registro no encontrado', 404);
        }
        return $this->successResponse($registro, 'Registro obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        // no hace nada al simplemente ser una tabla auxiliar para el muchos a muchos
    }

    public function destroy(string $id)
    {
        $registro = OperativoEspecialidadService::delete($id);
        if (!$registro) {
            return $this->errorResponse('Registro no encontrado', 404);
        }
        return $this->successResponse($registro, 'Registro eliminado correctamente');
    }
}
