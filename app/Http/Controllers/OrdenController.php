<?php

namespace App\Http\Controllers;

use App\Services\ServicioService;
use Illuminate\Http\Request;
use App\Services\OrdenService;
use App\Services\OrdenServicioService;
use App\Models\Cliente;
use App\Services\ServicioMaterialService;
use App\Services\ServicioTipoEquipoService;
use App\Services\ServicioEspecialidadService;
use App\Services\MailerService;
use App\Models\User;
use App\Services\ClienteService;
use App\Services\PresupuestoService;
use App\Services\NotificacionService;
use App\Services\OrdenServicioMaterialService;
use App\Services\OrdenServicioTipoEquipoService;
use App\Services\OrdenServicioEspecialidadService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Orden;
use App\Models\OrdenServicio;
use App\Models\OrdenServicioOperativo;
use App\Models\OrdenServicioEquipo;

class OrdenController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $ordenes = collect([]);

        if ($user->id_rol === '00003') {
            $ordenes = OrdenService::getAll();
        } elseif ($user->id_rol === '00001') {
            $cliente = Cliente::where('id_user', $user->id)->first();
            if ($cliente) {
                $ordenes = OrdenService::getOrdenesByCliente($cliente->id_cliente);
            }
        }

        return $this->successResponse(
            [
                'id_rol' => $user->id_rol,
                'ordenes' => $ordenes
            ],
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
            'fecha_inicio' => 'nullable|string|max:100',
            'fecha_fin' => 'nullable|string|max:100',
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
                $dataServicio['precio_materiales_unitario'] = $servicio['precio_materiales_unitario'];
                $dataServicio['precio_tipos_equipos_unitario'] = $servicio['precio_tipos_equipos_unitario'];
                $dataServicio['precio_mano_obra_unitario'] = $servicio['precio_mano_obra_unitario'];
                $dataServicio['precio_general_unitario'] = $servicio['precio_general_unitario'];
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

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $detalle = ($request->has('detalle')) ? $request->detalle : false;

        $orden = OrdenService::getOne($id);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }

        $orden->array_servicios = OrdenServicioService::getOneByOrden($id);

        if ($orden->id_presupuesto) {
            $orden->presupuesto = PresupuestoService::getOne($orden->id_presupuesto);
        }

        if ($detalle) {
            foreach ($orden->array_servicios as $servicio) {
                $servicio_tabulado = ServicioService::esTabulado($servicio->id_servicio);
                if ($servicio_tabulado) {
                    $servicio->array_materiales = ServicioMaterialService::getOneByServicio($servicio->id_servicio);
                    $servicio->array_tipos_equipos = ServicioTipoEquipoService::getOneByServicio($servicio->id_servicio);
                    $servicio->array_especialidades = ServicioEspecialidadService::getOneByServicio($servicio->id_servicio);
                }
            }
        }

        return $this->successResponse(
            [
                'id_rol' => $user->id_rol,
                'orden' => $orden
            ],
            'Orden obtenida correctamente'
        );
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
            'pdf_peritaje' => 'nullable|string|max:1000',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_cliente') && !$request->has('id_admin') && !$request->has('id_presupuesto') && !$request->has('direccion') && !$request->has('estado') && !$request->has('fecha_inicio') && !$request->has('fecha_fin') && !$request->has('fecha_inicio_real') && !$request->has('fecha_fin_real') && !$request->has('fecha_emision') && !$request->has('fecha_validacion') && !$request->has('observaciones') && !$request->has('calificacion') && !$request->has('pdf_peritaje')) {
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

    public function cancelarOrden(Request $request, $id)
    {
        $request->validate([
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();

        $orden = OrdenService::getOne($id);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }
        $orden->estado = 'Cancelada';
        $orden->fecha_validacion = date('Y-m-d');
        // si hay observaciones agregarla, si no dejar null
        $orden->observaciones = $data['observaciones'] ?? null;
        $orden->save();

        //obtener id_cliente para enviar correo notificando la accion
        $cliente = ClienteService::getOne($orden->id_cliente);
        $id_user_cliente = $cliente->id_user;
        $user = User::where('id', $id_user_cliente)->first();

        MailerService::enviarCorreo([
            'to' => [$user->email],
            'cc' => [],
            'bcc' => [],
        ], 'Orden cancelada', 'emails.cancelacion_orden', ['nombre' => $user->name, 'id_orden' => $orden->id_orden, 'observaciones' => $orden->observaciones]);

        //grabar registro en la tabla notificaciones
        $notificacion = NotificacionService::store([
            'id_user' => $user->id,
            'asunto' => 'Orden cancelada',
            'fecha_envio' => date('Y-m-d H:i:s'),
        ]);


        return $this->successResponse($orden, 'Orden cancelada correctamente');
    }

    public function aceptarOrden(Request $request, $id)
    {
        $request->validate([
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();

        $orden = OrdenService::getOne($id);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }
        $orden->estado = 'Aceptada';
        $orden->fecha_validacion = date('Y-m-d');
        $orden->observaciones = $data['observaciones'] ?? null;
        $orden->save();

        //obtener id_cliente para enviar correo notificando la accion
        $cliente = ClienteService::getOne($orden->id_cliente);
        $id_user_cliente = $cliente->id_user;
        $user = User::where('id', $id_user_cliente)->first();

        MailerService::enviarCorreo([
            'to' => [$user->email],
            'cc' => [],
            'bcc' => [],
        ], 'Orden aceptada', 'emails.aceptacion_orden', ['nombre' => $user->name, 'id_orden' => $orden->id_orden, 'observaciones' => $orden->observaciones]);

        //grabar registro en la tabla notificaciones
        $notificacion = NotificacionService::store([
            'id_user' => $user->id,
            'asunto' => 'Orden aceptada',
            'fecha_envio' => date('Y-m-d H:i:s'),
        ]);

        return $this->successResponse($orden, 'Orden aceptada correctamente');
    }

    public function subirPeritaje(Request $request, $id)
    {
        $request->validate([
            'pdf_peritaje' => 'required|file|mimes:pdf|max:10240', // max 10MB
        ]);

        $orden = OrdenService::getOne($id);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }

        if ($request->hasFile('pdf_peritaje')) {
            // Eliminar archivo anterior si existe
            if ($orden->pdf_peritaje) {
                Storage::disk('public')->delete($orden->pdf_peritaje);
            }

            $file = $request->file('pdf_peritaje');
            $filename = 'peritaje_' . $orden->id_orden . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('peritajes', $filename, 'public');

            $orden->pdf_peritaje = $path;
            $orden->save();

            return $this->successResponse($orden, 'Archivo de peritaje subido correctamente');
        }

        return $this->errorResponse('No se pudo subir el archivo', 400);
    }

    public function getOneOrdenAsignarPersonal($id)
    {
        $orden = OrdenService::getOne($id);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }

        $orden->array_servicios = OrdenServicioService::getOneByOrden($id);

        foreach ($orden->array_servicios as $servicio) {
            $servicio->array_materiales = OrdenServicioMaterialService::getOneByServicio($servicio->id_orden_servicio);
            $servicio->array_tipos_equipos = OrdenServicioTipoEquipoService::getOneByServicio($servicio->id_orden_servicio);
            $servicio->array_especialidades = OrdenServicioEspecialidadService::getOneByServicio($servicio->id_orden_servicio);
        }

        return $this->successResponse($orden, 'Orden obtenida correctamente');
    }

    public function asignarPersonal(Request $request, $id)
    {
        $payload = $request->json()->all();
        $servicios = $payload['servicios'] ?? [];

        DB::beginTransaction();
        try {
            $orden = Orden::find($id);
            if (!$orden) {
                return $this->errorResponse('Orden no encontrada', 404);
            }

            // 1. Actualizar orden principal con estado y fechas globales provistas por el frontend
            $orden->estado = 'En espera';
            $orden->fecha_inicio = $payload['fecha_inicio'] ?? $orden->fecha_inicio;
            $orden->fecha_fin = $payload['fecha_fin'] ?? $orden->fecha_fin;
            $orden->save();

            foreach ($servicios as $srvData) {
                $id_orden_servicio = $srvData['id_orden_servicio'];
                $ordenServicio = OrdenServicio::find($id_orden_servicio);

                // 2. Actualizar fechas del servicio (ya calculadas en el frontend)
                $ordenServicio->fecha_inicio = $srvData['fecha_inicio'] ?? null;
                $ordenServicio->fecha_fin = $srvData['fecha_fin'] ?? null;
                $ordenServicio->save();

                // 3. Crear operativos
                foreach ($srvData['operadores'] as $opData) {
                    OrdenServicioOperativo::create([
                        'id_orden_servicio' => $id_orden_servicio,
                        'id_operativo' => $opData['id_operativo'],
                        'id_especialidad' => $opData['id_especialidad'],
                        'fecha_inicio' => $opData['fecha_inicio'],
                        'fecha_fin' => $opData['fecha_fin'],
                        'es_jefe' => $opData['es_jefe'] ?? 0
                    ]);
                }

                // 4. Crear registros de equipos
                foreach ($srvData['equipos'] as $eqData) {
                    OrdenServicioEquipo::create([
                        'id_orden_servicio' => $id_orden_servicio,
                        'id_equipo' => $eqData['id_equipo'],
                        'fecha_inicio' => $eqData['fecha_inicio'],
                        'fecha_fin' => $eqData['fecha_fin']
                    ]);
                }
            }

            DB::commit();
            return $this->successResponse($orden, 'Asignación guardada con éxito.');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }
}
