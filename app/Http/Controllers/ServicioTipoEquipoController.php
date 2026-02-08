<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ServicioTipoEquipoService;

class ServicioTipoEquipoController extends Controller
{
    public function index()
    {
        $registros = ServicioTipoEquipoService::getAll();
        return $this->successResponse(
            $registros,
            $registros->isEmpty() ? 'No se encontraron registros' : 'Registros obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_servicio' => 'required|string|max:100',
            'id_tipo_equipo' => 'required|string|max:100',
            'cantidad' => 'required|numeric|min:1',
            'horas_uso' => 'required|numeric',
        ]);

        $data = $request->all();

        $registro = ServicioTipoEquipoService::store($data);
        if (!$registro) {
            return $this->errorResponse('No se pudo crear el registro', 404);
        }
        return $this->successResponse($registro, 'Registro creado correctamente');
    }

    public function show($id)
    {
        $registro = ServicioTipoEquipoService::getOne($id);
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
        $registro = ServicioTipoEquipoService::delete($id);
        if (!$registro) {
            return $this->errorResponse('Registro no encontrado', 404);
        }
        return $this->successResponse($registro, 'Registro eliminado correctamente');
    }
}
