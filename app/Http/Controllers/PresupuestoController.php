<?php

namespace App\Http\Controllers;

use App\Services\AdminService;
use Illuminate\Http\Request;
use App\Services\PresupuestoService;
use App\Services\OrdenService;
use App\Services\OrdenServicioService;
use App\Services\OrdenServicioMaterialService;
use App\Services\OrdenServicioTipoEquipoService;
use App\Services\OrdenServicioEspecialidadService;

class PresupuestoController extends Controller
{
    public function index()
    {
        $presupuestos = PresupuestoService::getAll();
        return $this->successResponse(
            $presupuestos,
            $presupuestos->isEmpty() ? 'No se encontraron presupuestos' : 'Presupuestos obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'id_orden' => 'required|string|max:100',
            'total_materiales' => 'required|numeric',
            'total_equipos' => 'required|numeric',
            'total_mano_obra' => 'required|numeric',
            'total_general' => 'required|numeric',
            'total_descuento' => 'required|numeric',
            'total_a_pagar' => 'required|numeric',
            'array_servicios' => 'required|array',
        ]);

        $data = $request->all();

        $admin = AdminService::getOneByUser($user->id);
        $data['id_admin'] = $admin->id_admin;

        $presupuesto = PresupuestoService::store($data);

        // actualizar campos id_admin, id_presupuesto y estado en la tabla ordenes
        $orden = OrdenService::getOne($data['id_orden']);
        $orden->id_admin = $admin->id_admin;
        $orden->id_presupuesto = $presupuesto->id_presupuesto;
        $orden->estado = 'Presupuestada';
        $orden->save();

        // Actualizar cada servicio de la orden
        foreach ($data['array_servicios'] as $servicioItem) {
            $ordenServicio = OrdenServicioService::getOne($servicioItem['id_orden_servicio']);

            if ($ordenServicio) {
                $ordenServicio->precio_materiales = $servicioItem['precio_materiales'];
                $ordenServicio->precio_tipos_equipos = $servicioItem['precio_tipos_equipos'];
                $ordenServicio->precio_mano_obra = $servicioItem['precio_mano_obra'];
                $ordenServicio->precio_general = $servicioItem['precio_general'];
                $ordenServicio->descuento = $servicioItem['descuento'];
                $ordenServicio->precio_a_pagar = $servicioItem['precio_a_pagar'];
                $ordenServicio->save();

                // guardar registros en la tabla ordenes_servicios_materiales si existe el array_materiales
                if (isset($servicioItem['array_materiales'])) {
                    foreach ($servicioItem['array_materiales'] as $material) {
                        $registro = [
                            'id_orden_servicio' => $ordenServicio->id_orden_servicio,
                            'id_material' => $material['id_material'],
                            'cantidad' => $material['cantidad'],
                            'precio_unitario' => $material['precio_unitario'],
                        ];
                        OrdenServicioMaterialService::store($registro);
                    }
                }

                // guardar registros en la tabla ordenes_servicios_tipos_equipos si existe el array_tipos_equipos
                if (isset($servicioItem['array_tipos_equipos'])) {
                    foreach ($servicioItem['array_tipos_equipos'] as $tipo_equipo) {
                        $registro = [
                            'id_orden_servicio' => $ordenServicio->id_orden_servicio,
                            'id_tipo_equipo' => $tipo_equipo['id_tipo_equipo'],
                            'cantidad' => $tipo_equipo['cantidad'],
                            'horas_uso' => $tipo_equipo['horas_uso'],
                            'costo_hora' => $tipo_equipo['costo_hora'],
                        ];
                        OrdenServicioTipoEquipoService::store($registro);
                    }
                }

                // guardar registros en la tabla ordenes_servicios_especialidades si existe el array_especialidades
                if (isset($servicioItem['array_especialidades'])) {
                    foreach ($servicioItem['array_especialidades'] as $especialidad) {
                        $registro = [
                            'id_orden_servicio' => $ordenServicio->id_orden_servicio,
                            'id_especialidad' => $especialidad['id_especialidad'],
                            'cantidad' => $especialidad['cantidad'],
                            'horas_hombre' => $especialidad['horas_hombre'],
                            'tarifa_hora' => $especialidad['tarifa_hora'],
                        ];
                        OrdenServicioEspecialidadService::store($registro);
                    }
                }
            }
        }

        if (!$presupuesto) {
            return $this->errorResponse('No se pudo crear el presupuesto', 404);
        }
        return $this->successResponse($presupuesto, 'Presupuesto creado correctamente');
    }

    public function show($id)
    {
        $presupuesto = PresupuestoService::getOne($id);
        if (!$presupuesto) {
            return $this->errorResponse('Presupuesto no encontrado', 404);
        }
        return $this->successResponse($presupuesto, 'Presupuesto obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_admin' => 'nullable|string|max:100',
            'total_materiales' => 'nullable|numeric',
            'total_equipos' => 'nullable|numeric',
            'total_mano_obra' => 'nullable|numeric',
            'total_general' => 'nullable|numeric',
            'total_descuento' => 'nullable|numeric',
            'total_a_pagar' => 'nullable|numeric',
            'estado' => 'nullable|string|max:50',
            'fecha_emision' => 'nullable|date',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_admin') && !$request->has('total_materiales') && !$request->has('total_equipos') && !$request->has('total_mano_obra') && !$request->has('total_general') && !$request->has('total_descuento') && !$request->has('total_a_pagar') && !$request->has('estado') && !$request->has('fecha_emision')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $presupuesto = PresupuestoService::update($id, $data);
        if (!$presupuesto) {
            return $this->errorResponse('Presupuesto no encontrado', 404);
        }

        return $this->successResponse($presupuesto, 'Presupuesto actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $presupuesto = PresupuestoService::delete($id);
        if (!$presupuesto) {
            return $this->errorResponse('Presupuesto no encontrado', 404);
        }
        return $this->successResponse($presupuesto, 'Presupuesto eliminado correctamente');
    }
}
