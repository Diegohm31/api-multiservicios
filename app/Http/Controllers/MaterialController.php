<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MaterialService;
use App\Services\AdminService;

class MaterialController extends Controller
{
    public function index()
    {
        $materiales = MaterialService::getAll();
        return $this->successResponse(
            $materiales,
            $materiales->isEmpty() ? 'No se encontraron materiales' : 'Materiales obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'string|max:100|required',
            'descripcion' => 'string|max:1000|required',
            'unidad_medida' => 'string|max:50|required',
            'stock_minimo' => 'numeric|required',
            'precio_unitario' => 'numeric|required',
        ]);

        $data = $request->all();

        $material = MaterialService::store($data);
        if (!$material) {
            return $this->errorResponse('Material no creado', 404);
        }

        return $this->successResponse($material, 'Material creado correctamente');
    }

    public function show($id)
    {
        $material = MaterialService::getOne($id);
        if (!$material) {
            return $this->errorResponse('Material no encontrado', 404);
        }
        return $this->successResponse($material, 'Material obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'string|max:100',
            'descripcion' => 'string|max:1000',
            'unidad_medida' => 'string|max:50',
            'stock_minimo' => 'numeric',
            'precio_unitario' => 'numeric',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('nombre') && !$request->has('descripcion') && !$request->has('unidad_medidad') && !$request->has('stock_minimo') && !$request->has('precio_unitario')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }

        $data = $request->all();

        $material = MaterialService::update($id, $data);
        if (!$material) {
            return $this->errorResponse('Material no encontrado', 404);
        }

        return $this->successResponse($material, 'Material actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $material = MaterialService::delete($id);
        if (!$material) {
            return $this->errorResponse('Material no encontrado', 404);
        }
        return $this->successResponse($material, 'Material eliminado correctamente');
    }
}
