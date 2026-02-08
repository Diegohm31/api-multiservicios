<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdminService;
use App\Http\Controllers\Controller;
use App\Services\UserService;

class AdminController extends Controller
{
    public function index()
    {
        $admins = AdminService::getAll();
        return $this->successResponse(
            $admins,
            $admins->isEmpty() ? 'No se encontraron admins' : 'Admins obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|max:255',
            'cedula' => 'required|string|max:11|unique:admins,cedula',
            'telefono' => 'string|max:11|unique:admins,telefono',
            'cargo' => 'required|string|max:100',
        ]);

        $data = $request->all();
        //asignar rol de admin
        $data['id_rol'] = "00003";

        $user = UserService::store($data);
        $data['id_user'] = $user->id;

        $admin = AdminService::create($data);
        if (!$admin) {
            return $this->errorResponse('Admin no creado', 404);
        }

        return $this->successResponse($admin, 'Admin creado correctamente');
    }

    public function show($id)
    {
        $admin = AdminService::getOne($id);
        if (!$admin) {
            return $this->errorResponse('Admin no encontrado', 404);
        }
        return $this->successResponse($admin, 'Admin obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'string|max:100',
            'cedula' => 'string|max:11|unique:admins,cedula',
            'telefono' => 'string|max:11|unique:admins,telefono',
            'cargo' => 'string|max:100',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('nombre') && !$request->has('cedula') && !$request->has('telefono') && !$request->has('cargo')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }

        $data = $request->all();

        $admin = AdminService::update($id, $data);
        if (!$admin) {
            return $this->errorResponse('Admin no encontrado', 404);
        }

        return $this->successResponse($admin, 'Admin actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $admin = AdminService::delete($id);
        if (!$admin) {
            return $this->errorResponse('Admin no encontrado', 404);
        }
        return $this->successResponse($admin, 'Admin eliminado correctamente');
    }
}
