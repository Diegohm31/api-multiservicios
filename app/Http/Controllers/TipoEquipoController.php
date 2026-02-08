<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TipoEquipoService;

class TipoEquipoController extends Controller
{
    public function index()
    {
        $tipos_equipos = TipoEquipoService::getAll();
        return $this->successResponse(
            $tipos_equipos,
            $tipos_equipos->isEmpty() ? 'No se encontraron tipos de equipos' : 'Tipos de equipos obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'costo_hora' => 'required|numeric',
        ]);

        $data = $request->all();

        $tipo_equipo = TipoEquipoService::store($data);
        if (!$tipo_equipo) {
            return $this->errorResponse('No se pudo crear el tipo de equipo', 404);
        }
        return $this->successResponse($tipo_equipo, 'Tipo de equipo creado correctamente');
    }

    public function show($id)
    {
        $tipo_equipo = TipoEquipoService::getOne($id);
        if (!$tipo_equipo) {
            return $this->errorResponse('Tipo de equipo no encontrado', 404);
        }
        return $this->successResponse($tipo_equipo, 'Tipo de equipo obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'string|max:100',
            'costo_hora' => 'numeric',
            'cantidad' => 'integer',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('nombre') && !$request->has('costo_hora') && !$request->has('cantidad')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $tipo_equipo = TipoEquipoService::update($id, $data);
        if (!$tipo_equipo) {
            return $this->errorResponse('Tipo de equipo no encontrado', 404);
        }

        return $this->successResponse($tipo_equipo, 'Tipo de equipo actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $tipo_equipo = TipoEquipoService::delete($id);
        if (!$tipo_equipo) {
            return $this->errorResponse('Tipo de equipo no encontrado', 404);
        }
        return $this->successResponse($tipo_equipo, 'Tipo de equipo eliminado correctamente');
    }
}
