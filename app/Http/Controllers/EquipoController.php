<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EquipoService;

class EquipoController extends Controller
{
    public function index()
    {
        $equipos = EquipoService::getAll();
        return $this->successResponse(
            $equipos,
            $equipos->isEmpty() ? 'No se encontraron equipos' : 'Equipos obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_tipo_equipo' => 'required|exists:tipos_equipos,id_tipo_equipo',
            'modelo' => 'required|string|max:100',
            'descripcion' => 'required|string|max:1000',
            'codigo_interno' => 'required|string|max:100',
            'fecha_adquisicion' => 'required|date',
        ]);

        $data = $request->all();

        $equipo = EquipoService::store($data);
        if (!$equipo) {
            return $this->errorResponse('No se pudo crear el equipo', 404);
        }
        return $this->successResponse($equipo, 'Equipo creado correctamente');
    }

    public function show($id)
    {
        $equipo = EquipoService::getOne($id);
        if (!$equipo) {
            return $this->errorResponse('Equipo no encontrado', 404);
        }
        
        // Adjuntamos flag para que el frontend pueda bloquear modificaciones al campo de tipo_equipo
        $equipo->tiene_asignaciones_previas = EquipoService::tieneAsignacionesPrevias($id);
        
        return $this->successResponse($equipo, 'Equipo obtenido correctamente');
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_tipo_equipo' => 'exists:tipos_equipos,id_tipo_equipo',
            'modelo' => 'string|max:100',
            'descripcion' => 'string|max:1000',
            'codigo_interno' => 'string|max:100',
            'disponible' => 'boolean',
            'fecha_adquisicion' => 'date',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_tipo_equipo') && !$request->has('modelo') && !$request->has('descripcion') && !$request->has('codigo_interno') && !$request->has('disponible') && !$request->has('fecha_adquisicion')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        
        $equipoAntiguo = EquipoService::getOne($id);
        if (!$equipoAntiguo) {
            return $this->errorResponse('Equipo no encontrado', 404);
        }

        // Si intenta cambiar la categoría, verificamos que no tenga historial
        if ($request->has('id_tipo_equipo') && $request->id_tipo_equipo != $equipoAntiguo->id_tipo_equipo) {
            if (EquipoService::tieneAsignacionesPrevias($id)) {
                return $this->errorResponse('No se puede modificar la categoría de un equipo que ya ha sido asignado a órdenes.', 400);
            }
        }

        $data = $request->all();
        $equipo = EquipoService::update($id, $data);

        return $this->successResponse($equipo, 'Equipo actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $equipo = EquipoService::delete($id);
        
        if ($equipo === false) {
            return $this->errorResponse('No se puede eliminar el equipo porque está siendo utilizado en una orden en ejecución.', 400);
        }
        
        if (!$equipo) {
            return $this->errorResponse('Equipo no encontrado', 404);
        }
        
        return $this->successResponse($equipo, 'Equipo eliminado correctamente');
    }
}
