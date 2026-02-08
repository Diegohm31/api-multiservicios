<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MovimientoMaterialService;
use App\Services\AdminService;

class MovimientoMaterialController extends Controller
{
    public function index()
    {
        $materiales = MovimientoMaterialService::getAll();
        return $this->successResponse(
            $materiales,
            $materiales->isEmpty() ? 'No se encontraron movimientos de materiales' : 'Movimientos de materiales obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'movimientos' => 'required|array|min:1',
            'movimientos.*.id_material' => 'required|string|exists:materiales,id_material',
            'movimientos.*.tipo_movimiento' => 'required|string',
            'movimientos.*.cantidad' => 'required|numeric|min:0.01',
            'movimientos.*.motivo' => 'required|string|max:1000',
        ]);

        $data = $request->all();
        $user = $request->user();
        $admin = AdminService::getOneByUser($user->id);
        $data['id_admin'] = $admin->id_admin;

        //ciclo llamando al servicio para registrar cada movimiento
        foreach ($data['movimientos'] as $movimiento) {
            $movimiento['id_admin'] = $data['id_admin'];
            $movimiento_registrado = MovimientoMaterialService::store($movimiento);
            if (!$movimiento_registrado) {
                return $this->errorResponse('Movimiento de material no creado', 404);
            }
        }

        return $this->successResponse($data, 'Movimientos de materiales creados correctamente');
    }

    public function show($id)
    {
        $material = MovimientoMaterialService::getOne($id);
        if (!$material) {
            return $this->errorResponse('Movimiento de material no encontrado', 404);
        }
        return $this->successResponse($material, 'Movimiento de material obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_material' => 'string|max:100',
            'id_admin' => 'string|max:100',
            'tipo_movimiento' => 'string|max:100',
            'cantidad' => 'numeric',
            'motivo' => 'string|max:1000',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_material') && !$request->has('id_admin') && !$request->has('tipo_movimiento') && !$request->has('cantidad') && !$request->has('motivo')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }

        $data = $request->all();

        $material = MovimientoMaterialService::update($id, $data);
        if (!$material) {
            return $this->errorResponse('Movimiento de material no encontrado', 404);
        }

        return $this->successResponse($material, 'Movimiento de material actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $material = MovimientoMaterialService::delete($id);
        if (!$material) {
            return $this->errorResponse('Movimiento de material no encontrado', 404);
        }
        return $this->successResponse($material, 'Movimiento de material eliminado correctamente');
    }
}
