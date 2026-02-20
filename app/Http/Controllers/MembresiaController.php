<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MembresiaService;

class MembresiaController extends Controller
{
    public function index()
    {
        $membresias = MembresiaService::getAll();
        return $this->successResponse(
            $membresias,
            $membresias->isEmpty() ? 'No se encontraron membresias' : 'Membresias obtenidas correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_cliente' => 'required|string|max:100|exists:clientes,id_cliente',
            'id_plan_membresia' => 'required|string|max:100|exists:planes_membresias,id_plan_membresia',
            'precio' => 'required|numeric',
        ]);

        $data = $request->all();

        $membresia = MembresiaService::store($data);
        if (!$membresia) {
            return $this->errorResponse('No se pudo crear la membresia', 404);
        }
        return $this->successResponse($membresia, 'Membresia creada correctamente');
    }

    public function show($id)
    {
        $membresia = MembresiaService::getOne($id);
        if (!$membresia) {
            return $this->errorResponse('Membresia no encontrada', 404);
        }
        return $this->successResponse($membresia, 'Membresia obtenida correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_cliente' => 'nullable|string|max:100|exists:clientes,id_cliente',
            'id_plan_membresia' => 'nullable|string|max:100|exists:planes_membresias,id_plan_membresia',
            'precio' => 'nullable|numeric',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'estado' => 'nullable|string|max:100',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_cliente') && !$request->has('id_plan_membresia') && !$request->has('precio') && !$request->has('fecha_inicio') && !$request->has('fecha_fin') && !$request->has('estado')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $membresia = MembresiaService::update($id, $data);
        if (!$membresia) {
            return $this->errorResponse('Membresia no encontrada', 404);
        }

        return $this->successResponse($membresia, 'Membresia actualizada correctamente');
    }

    public function destroy(string $id)
    {
        $membresia = MembresiaService::delete($id);
        if (!$membresia) {
            return $this->errorResponse('Membresia no encontrada', 404);
        }
        return $this->successResponse($membresia, 'Membresia eliminada correctamente');
    }
}
