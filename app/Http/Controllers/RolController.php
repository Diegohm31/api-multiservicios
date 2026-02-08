<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RolService;

class RolController extends Controller
{
    public function index()
    {
        $roles = RolService::getAll();
        return $this->successResponse(
            $roles,
            $roles->isEmpty() ? 'No se encontraron roles' : 'Roles obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        $data = $request->all();

        $rol = RolService::store($data);
        if (!$rol) {
            return $this->errorResponse('No se pudo crear el rol', 404);
        }
        return $this->successResponse($rol, 'Rol creado correctamente');
    }

    public function show($id)
    {
        $rol = RolService::getOne($id);
        if (!$rol) {
            return $this->errorResponse('Rol no encontrado', 404);
        }
        return $this->successResponse($rol, 'Rol obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'string|max:100',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('nombre')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $rol = RolService::update($id, $data);
        if (!$rol) {
            return $this->errorResponse('Rol no encontrado', 404);
        }

        return $this->successResponse($rol, 'Rol actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $rol = RolService::delete($id);
        if (!$rol) {
            return $this->errorResponse('Rol no encontrado', 404);
        }
        return $this->successResponse($rol, 'Rol eliminado correctamente');
    }
}
