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
use App\Services\ClienteService;
use App\Models\User;
use App\Services\MailerService;
use App\Services\NotificacionService;
use App\Services\PDFService;

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
            'sub_total' => 'required|numeric',
            'porcentaje_iva' => 'required|numeric',
            'iva' => 'required|numeric',
            'total_a_pagar' => 'required|numeric',
            'array_servicios' => 'required|array',
            'array_servicios.*.array_especialidades' => 'required|array|min:1',
        ], [
            'array_servicios.*.array_especialidades.required' => 'Cada servicio debe tener al menos una especialidad (Mano de Obra) asignada.',
            'array_servicios.*.array_especialidades.min' => 'Cada servicio debe tener al menos una especialidad (Mano de Obra) asignada.',
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
                $ordenServicio->precio_materiales_unitario = $servicioItem['precio_materiales_unitario'];
                $ordenServicio->precio_tipos_equipos_unitario = $servicioItem['precio_tipos_equipos_unitario'];
                $ordenServicio->precio_mano_obra_unitario = $servicioItem['precio_mano_obra_unitario'];
                $ordenServicio->precio_general_unitario = $servicioItem['precio_general_unitario'];
                $ordenServicio->porcentaje_descuento = $servicioItem['porcentaje_descuento'];
                $ordenServicio->descuento_unitario = $servicioItem['descuento_unitario'];
                $ordenServicio->precio_neto_unitario = $servicioItem['precio_neto_unitario'];
                $ordenServicio->precio_a_pagar = $servicioItem['precio_a_pagar'];
                $ordenServicio->save();

                // Eliminar registros previos de pivotes
                \Illuminate\Support\Facades\DB::table('ordenes_servicios_materiales')->where('id_orden_servicio', $ordenServicio->id_orden_servicio)->delete();
                \Illuminate\Support\Facades\DB::table('ordenes_servicios_tipos_equipos')->where('id_orden_servicio', $ordenServicio->id_orden_servicio)->delete();
                \Illuminate\Support\Facades\DB::table('ordenes_servicios_especialidades')->where('id_orden_servicio', $ordenServicio->id_orden_servicio)->delete();

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

        //obtener id_cliente para enviar correo notificando la accion
        $cliente = ClienteService::getOne($orden->id_cliente);
        $id_user_cliente = $cliente->id_user;
        $user = User::where('id', $id_user_cliente)->first();

        // obtener dominio host y protocolo http o https que hizo el request
        $origin = $request->header('Origin');

        // $url = 'http://multiservicios.local/servicios/orden/detalles/' . $orden->id_orden;
        $url = $origin . '/servicios/orden/detalles/' . $orden->id_orden;

        MailerService::enviarCorreo([
            'to' => [$user->email],
            'cc' => [],
            'bcc' => [],
        ], 'Presupuesto creado', 'emails.presupuesto_creado', ['nombre' => $user->name, 'id_orden' => $orden->id_orden, 'url' => $url, 'fecha_emision' => $presupuesto->fecha_emision, 'fecha_vencimiento' => $presupuesto->fecha_vencimiento]);

        //grabar registro en la tabla notificaciones
        $notificacion = NotificacionService::store([
            'id_user' => $user->id,
            'asunto' => 'Presupuesto creado',
            'fecha_envio' => date('Y-m-d H:i:s'),
        ]);

        // Generar PDF automáticamente (Ahora que la orden y servicios están vinculados)
        $empresa = \App\Models\Empresa::first();
        $serviciosPDF = OrdenServicioService::getOneByOrden($orden->id_orden);

        foreach ($serviciosPDF as $servicioPDF) {
            $servicioPDF->array_materiales = OrdenServicioMaterialService::getOneByServicio($servicioPDF->id_orden_servicio);
            $servicioPDF->array_tipos_equipos = OrdenServicioTipoEquipoService::getOneByServicio($servicioPDF->id_orden_servicio);
            $servicioPDF->array_especialidades = OrdenServicioEspecialidadService::getOneByServicio($servicioPDF->id_orden_servicio);
        }

        $pdfPath = PDFService::generarPresupuestoPDF($presupuesto, $orden, $cliente, $empresa, $serviciosPDF);
        if ($pdfPath) {
            $presupuesto->pdf_factura = $pdfPath;
            $presupuesto->save();
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
            'sub_total' => 'nullable|numeric',
            'porcentaje_iva' => 'nullable|numeric',
            'iva' => 'nullable|numeric',
            'total_a_pagar' => 'nullable|numeric',
            'pdf_factura' => 'nullable|string|max:1000',
            'estado' => 'nullable|string|max:50',
            'fecha_emision' => 'nullable|date',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_admin') && !$request->has('total_materiales') && !$request->has('total_equipos') && !$request->has('total_mano_obra') && !$request->has('total_general') && !$request->has('total_descuento') && !$request->has('sub_total') && !$request->has('porcentaje_iva') && !$request->has('iva') && !$request->has('total_a_pagar') && !$request->has('pdf_factura') && !$request->has('estado') && !$request->has('fecha_emision')) {
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

    public function aceptarPresupuesto(string $id_orden)
    {
        $orden = OrdenService::getOne($id_orden);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }
        $orden->estado = 'Por pagar';
        $orden->save();

        $presupuesto = PresupuestoService::getOne($orden->id_presupuesto);
        if (!$presupuesto) {
            return $this->errorResponse('Presupuesto no encontrado', 404);
        }
        $presupuesto->estado = 'Aceptado';
        $presupuesto->save();
        return $this->successResponse($presupuesto, 'Presupuesto aceptado correctamente');
    }

    public function cancelarPresupuesto(string $id_orden)
    {
        $orden = OrdenService::getOne($id_orden);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }
        $id_presupuesto = $orden->id_presupuesto;
        $orden->estado = 'Aceptada';
        $orden->id_presupuesto = null;
        $orden->save();

        $presupuesto = PresupuestoService::getOne($id_presupuesto);
        if (!$presupuesto) {
            return $this->errorResponse('Presupuesto no encontrado', 404);
        }
        $presupuesto->estado = 'Cancelado';
        $presupuesto->save();
        return $this->successResponse($presupuesto, 'Presupuesto cancelado correctamente');
    }
}
