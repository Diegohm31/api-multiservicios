<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TipoServicioService;

class TipoServicioController extends Controller
{
    public function index()
    {
        $tipos_servicios = TipoServicioService::getAll();
        return $this->successResponse(
            $tipos_servicios,
            $tipos_servicios->isEmpty() ? 'No se encontraron tipos de servicios' : 'Tipos de servicios obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->all();

        $tipo_servicio = TipoServicioService::store($data);
        if (!$tipo_servicio) {
            return $this->errorResponse('No se pudo crear el tipo de servicio', 404);
        }
        return $this->successResponse($tipo_servicio, 'Tipo de servicio creado correctamente');
    }

    public function show($id)
    {
        $tipo_servicio = TipoServicioService::getOne($id);
        if (!$tipo_servicio) {
            return $this->errorResponse('Tipo de servicio no encontrado', 404);
        }
        return $this->successResponse($tipo_servicio, 'Tipo de servicio obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'string|max:255',
            'descripcion' => 'string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('nombre') && !$request->has('descripcion') && !$request->hasFile('image')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $tipo_servicio = TipoServicioService::update($id, $data);
        if (!$tipo_servicio) {
            return $this->errorResponse('Tipo de servicio no encontrado', 404);
        }

        return $this->successResponse($tipo_servicio, 'Tipo de servicio actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $tipo_servicio = TipoServicioService::delete($id);
        if (!$tipo_servicio) {
            return $this->errorResponse('Tipo de servicio no encontrado', 404);
        }
        return $this->successResponse($tipo_servicio, 'Tipo de servicio eliminado correctamente');
    }
}
