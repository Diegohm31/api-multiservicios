<?php

namespace App\Http\Controllers;

use App\Services\EspecialidadService;
use Illuminate\Http\Request;
use App\Services\OperativoService;
use App\Services\UserService;
use App\Services\OperativoEspecialidadService;

class OperativoController extends Controller
{
    public function index()
    {
        $operativos = OperativoService::getAll();

        //ciclo obteniendo el array_especialidades de cada operativo
        foreach ($operativos as $operativo) {
            $operativo->array_especialidades = OperativoEspecialidadService::getOneByOperativo($operativo->id_operativo);
        }

        return $this->successResponse(
            $operativos,
            $operativos->isEmpty() ? 'No se encontraron operativos' : 'Operativos obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|max:255',
            'cedula' => 'required|string|max:11|unique:operativos,cedula',
            'telefono' => 'string|max:11|unique:operativos,telefono',
            //validar que llegue un arreglo de id_especialidad
            'array_especialidades' => 'required|array',
        ]);

        $data = $request->all();
        //asignar rol de operativo
        $data['id_rol'] = "00002";

        $user = UserService::store($data);
        $data['id_user'] = $user->id;

        $operativo = OperativoService::create($data);

        //asignar especialidades
        foreach ($request->array_especialidades as $especialidad) {
            $data = [
                'id_operativo' => $operativo->id_operativo,
                'id_especialidad' => $especialidad,
            ];
            OperativoEspecialidadService::store($data);
            // sumar 1 en el campo cantidad de esa especialidad
            EspecialidadService::update($especialidad, ['cantidad' => 1]);
        }

        if (!$operativo) {
            return $this->errorResponse('Operativo no creado', 404);
        }

        return $this->successResponse($operativo, 'Operativo creado correctamente');
    }

    public function show($id)
    {
        $operativo = OperativoService::getOne($id);
        if (!$operativo) {
            return $this->errorResponse('Operativo no encontrado', 404);
        }

        //obtener el email del operativo
        $user = UserService::getOneById($operativo->id_user);
        $operativo->email = $user->email;

        $operativo->array_especialidades = OperativoEspecialidadService::getOneByOperativo($id);
        return $this->successResponse($operativo, 'Operativo obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|max:255',
            'cedula' => 'string|max:11',
            'telefono' => 'string|max:11',
            'disponible' => 'boolean',
            'reputacion' => 'numeric',
            'array_especialidades' => 'array',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('name') && !$request->has('email') && !$request->has('cedula') && !$request->has('telefono') && !$request->has('disponible') && !$request->has('reputacion') && !$request->has('array_especialidades')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }

        $data = $request->all();

        $data['nombre'] = $data['name'];
        $operativo = OperativoService::update($id, $data);
        if (!$operativo) {
            return $this->errorResponse('Operativo no encontrado', 404);
        }

        //actualizar en la tabla user
        $user = UserService::update($operativo->id_user, $data);

        $idsExistentes = OperativoEspecialidadService::getOneByOperativo($id)->pluck('id_especialidad')->toArray();
        $idsNuevos = $request->array_especialidades ?? [];

        $idsPorEliminar = array_diff($idsExistentes, $idsNuevos);
        $idsPorAgregar = array_diff($idsNuevos, $idsExistentes);

        //borrar registros de operativo_especialidad de ese operativo en especifico
        foreach ($idsPorEliminar as $idPorEliminar) {
            OperativoEspecialidadService::deleteByEspecialidadAndOperativo($idPorEliminar, $id);
            // restar 1 en el campo cantidad de esa especialidad
            EspecialidadService::update($idPorEliminar, ['cantidad' => -1]);
        }

        //insertar registros de operativo_especialidad de ese operativo en especifico
        foreach ($idsPorAgregar as $idPorAgregar) {
            $data = [
                'id_operativo' => $id,
                'id_especialidad' => $idPorAgregar,
            ];
            OperativoEspecialidadService::store($data);
            // sumar 1 en el campo cantidad de esa especialidad
            EspecialidadService::update($idPorAgregar, ['cantidad' => 1]);
        }

        return $this->successResponse($operativo, 'Operativo actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $operativo = OperativoService::delete($id);

        if (!$operativo) {
            return $this->errorResponse('Operativo no encontrado', 404);
        }

        $idsExistentes = OperativoEspecialidadService::getOneByOperativo($id)->pluck('id_especialidad')->toArray();

        //restar 1 en el campo cantidad de esa especialidad
        foreach ($idsExistentes as $idExistente) {
            EspecialidadService::update($idExistente, ['cantidad' => -1]);
        }

        $id_user = $operativo->id_user;

        $user = UserService::delete($id_user);

        //borrar token del usuario
        $user->tokens()->delete();

        return $this->successResponse($operativo, 'Operativo eliminado correctamente');
    }
}
