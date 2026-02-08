<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ClienteService;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = ClienteService::getAll();
        return $this->successResponse(
            $clientes,
            $clientes->isEmpty() ? 'No se encontraron clientes' : 'Clientes obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        // la creacion de nuevos clientes se hara exclusivamente por la funcion register de AuthController
    }

    public function show($id)
    {
        $cliente = ClienteService::getOne($id);
        if (!$cliente) {
            return $this->errorResponse('Cliente no encontrado', 404);
        }
        return $this->successResponse($cliente, 'Cliente obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'string|max:100',
            'cedula' => 'string|max:11|unique:clientes,cedula',
            'telefono' => 'string|max:11|unique:clientes,telefono',
            'direccion' => 'string|max:100',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('nombre') && !$request->has('cedula') && !$request->has('telefono') && !$request->has('direccion')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }

        $data = $request->all();

        $cliente = ClienteService::update($id, $data);
        if (!$cliente) {
            return $this->errorResponse('Cliente no encontrado', 404);
        }

        return $this->successResponse($cliente, 'Cliente actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $cliente = ClienteService::delete($id);
        if (!$cliente) {
            return $this->errorResponse('Cliente no encontrado', 404);
        }
        return $this->successResponse($cliente, 'Cliente eliminado correctamente');
    }

    // public function enviarPromocion()
    // {
    //     $clientes = ClienteService::enviarPromocion();
    //     if (!$clientes) {
    //         return $this->errorResponse('No se pudo enviar la promoción', 404);
    //     }
    //     return $this->successResponse($clientes, 'Promoción enviada correctamente');
    // }
}
