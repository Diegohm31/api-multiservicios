<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrdenService;
use App\Services\OrdenServicioService;

class OrdenController extends Controller
{
    public function index()
    {
        $ordenes = OrdenService::getAll();
        return $this->successResponse(
            $ordenes,
            $ordenes->isEmpty() ? 'No se encontraron ordenes' : 'Ordenes obtenidas correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_cliente' => 'required|string|max:100',
            'id_admin' => 'nullable|string|max:100',
            'id_presupuesto' => 'nullable|string|max:100',
            'direccion' => 'required|string|max:1000',
            'estado' => 'required|string|max:100',
            'fecha_inicio' => 'required|string|max:100',
            'fecha_fin' => 'required|string|max:100',
            'fecha_inicio_real' => 'nullable|string|max:100',
            'fecha_fin_real' => 'nullable|string|max:100',
            'fecha_emision' => 'required|string|max:100',
            'fecha_validacion' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:1000',
            'calificacion' => 'nullable|int|max:5',
            'array_servicios' => 'required|array',
        ]);

        $data = $request->all();

        $orden = OrdenService::store($data);

        //crear registros en la tabla ordenes_servicios
        foreach ($data['array_servicios'] as $servicio) {

            $dataServicio = [
                'id_orden' => $orden->id_orden,
                'id_servicio' => $servicio['id_servicio'],
                'descripcion' => $servicio['descripcion'],
                'cantidad' => $servicio['cantidad'],
            ];

            if ($servicio['servicio_tabulado'] == 1) {
                $dataServicio['precio_materiales'] = $servicio['precio_materiales'];
                $dataServicio['precio_tipos_equipos'] = $servicio['precio_tipos_equipos'];
                $dataServicio['precio_mano_obra'] = $servicio['precio_mano_obra'];
                $dataServicio['precio_general'] = $servicio['precio_general'];
                $dataServicio['descuento'] = $servicio['descuento'];
                $dataServicio['precio_a_pagar'] = $servicio['precio_a_pagar'];
            }

            $orden_servicio = OrdenServicioService::store($dataServicio);
        }

        if (!$orden) {
            return $this->errorResponse('No se pudo crear la orden', 404);
        }
        return $this->successResponse($orden, 'Orden creada correctamente');
    }

    public function show($id)
    {
        $orden = OrdenService::getOne($id);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }
        return $this->successResponse($orden, 'Orden obtenida correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_cliente' => 'nullable|string|max:100',
            'id_admin' => 'nullable|string|max:100',
            'id_presupuesto' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:1000',
            'estado' => 'nullable|string|max:100',
            'fecha_inicio' => 'nullable|string|max:100',
            'fecha_fin' => 'nullable|string|max:100',
            'fecha_inicio_real' => 'nullable|string|max:100',
            'fecha_fin_real' => 'nullable|string|max:100',
            'fecha_emision' => 'nullable|string|max:100',
            'fecha_validacion' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:1000',
            'calificacion' => 'nullable|int|max:5',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_cliente') && !$request->has('id_admin') && !$request->has('id_presupuesto') && !$request->has('direccion') && !$request->has('estado') && !$request->has('fecha_inicio') && !$request->has('fecha_fin') && !$request->has('fecha_inicio_real') && !$request->has('fecha_fin_real') && !$request->has('fecha_emision') && !$request->has('fecha_validacion') && !$request->has('observaciones') && !$request->has('calificacion')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $orden = OrdenService::update($id, $data);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }

        return $this->successResponse($orden, 'Orden actualizada correctamente');
    }

    public function destroy(string $id)
    {
        $orden = OrdenService::delete($id);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }
        return $this->successResponse($orden, 'Orden eliminada correctamente');
    }
}
