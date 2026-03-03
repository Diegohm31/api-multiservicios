<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmpresaService;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresas = EmpresaService::getAll();
        return $this->successResponse(
            $empresas,
            $empresas->isEmpty() ? 'No se encontraron empresas' : 'Empresas obtenidas correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:1000',
            'correo' => 'required|string|max:100',
            'telefono' => 'required|string|max:100',
            'direccion' => 'required|string|max:1000',
            'rif' => 'required|string|max:100',
            'porcentaje_iva' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $data = $request->all();

        $empresa = EmpresaService::store($data);
        if (!$empresa) {
            return $this->errorResponse('No se pudo crear la empresa', 404);
        }
        return $this->successResponse($empresa, 'Empresa creada correctamente');
    }

    public function show($id)
    {
        $empresa = EmpresaService::getOne($id);
        if (!$empresa) {
            return $this->errorResponse('Empresa no encontrada', 404);
        }
        return $this->successResponse($empresa, 'Empresa obtenida correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'nullable|string|max:1000',
            'correo' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:1000',
            'rif' => 'nullable|string|max:100',
            'porcentaje_iva' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('nombre') && !$request->has('correo') && !$request->has('telefono') && !$request->has('direccion') && !$request->has('rif') && !$request->has('porcentaje_iva') && !$request->has('image')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $empresa = EmpresaService::update($id, $data);
        if (!$empresa) {
            return $this->errorResponse('Empresa no encontrada', 404);
        }

        return $this->successResponse($empresa, 'Empresa actualizada correctamente');
    }

    public function destroy(string $id)
    {
        $empresa = EmpresaService::delete($id);
        if (!$empresa) {
            return $this->errorResponse('Empresa no encontrada', 404);
        }
        return $this->successResponse($empresa, 'Empresa eliminada correctamente');
    }
}
