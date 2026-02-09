<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrdenServicioService;

class OrdenServicioController extends Controller
{
    public function index()
    {
        $ordenes = OrdenServicioService::getAll();
        return $this->successResponse(
            $ordenes,
            $ordenes->isEmpty() ? 'No se encontraron ordenes' : 'Ordenes obtenidas correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_orden' => 'required|string|max:100',
            'id_servicio' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:1000',
            'cantidad' => 'required|numeric',
            'precio_materiales' => 'nullable|numeric',
            'precio_tipos_equipos' => 'nullable|numeric',
            'precio_mano_obra' => 'nullable|numeric',
            'precio_general' => 'nullable|numeric',
            'descuento' => 'nullable|numeric',
            'precio_a_pagar' => 'nullable|numeric',
            'pdf_peritaje' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();

        $orden = OrdenServicioService::store($data);



        if (!$orden) {
            return $this->errorResponse('No se pudo crear la orden', 404);
        }
        return $this->successResponse($orden, 'Orden creada correctamente');
    }

    public function show($id)
    {
        $orden = OrdenServicioService::getOne($id);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }
        return $this->successResponse($orden, 'Orden obtenida correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_orden' => 'nullable|string|max:100',
            'id_servicio' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string|max:1000',
            'cantidad' => 'nullable|numeric',
            'precio_materiales' => 'nullable|numeric',
            'precio_tipos_equipos' => 'nullable|numeric',
            'precio_mano_obra' => 'nullable|numeric',
            'precio_general' => 'nullable|numeric',
            'descuento' => 'nullable|numeric',
            'precio_a_pagar' => 'nullable|numeric',
            'pdf_peritaje' => 'nullable|string|max:1000',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_orden') && !$request->has('id_servicio') && !$request->has('descripcion') && !$request->has('cantidad') && !$request->has('precio_materiales') && !$request->has('precio_equipos') && !$request->has('precio_mano_obra') && !$request->has('precio_general') && !$request->has('descuento') && !$request->has('precio_a_pagar') && !$request->has('pdf_peritaje')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $orden = OrdenServicioService::update($id, $data);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }

        return $this->successResponse($orden, 'Orden actualizada correctamente');
    }

    public function destroy(string $id)
    {
        $orden = OrdenServicioService::delete($id);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }
        return $this->successResponse($orden, 'Orden eliminada correctamente');
    }
}
