<?php

namespace App\Http\Controllers;

use App\Services\MaterialService;
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
use App\Services\UserService;
use App\Services\MovimientoMaterialService;
use App\Services\OrdenServicioMaterialService;
use App\Services\OrdenServicioTipoEquipoService;
use App\Services\OrdenServicioEspecialidadService;
use App\Services\OrdenServicioOperativoService;
use App\Services\OrdenServicioEquipoService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Orden;
use App\Models\Operativo;
use App\Models\OrdenServicio;
use App\Models\OrdenServicioOperativo;
use App\Models\OrdenServicioEquipo;
use App\Services\AvanceOrdenService;
use App\Services\OperativoService;

class OrdenController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $ordenes = collect([]);

        if ($user->id_rol === '00003') {
            $ordenes = OrdenService::getAll();
        }
        elseif ($user->id_rol === '00001') {
            $cliente = Cliente::where('id_user', $user->id)->first();
            if ($cliente) {
                $ordenes = OrdenService::getOrdenesByCliente($cliente->id_cliente);
            }
        }
        elseif ($user->id_rol === '00002') {
            $operativo = Operativo::where('id_user', $user->id)->first();
            if ($operativo) {
                $ordenes = OrdenService::getOrdenesByOperativo($operativo->id_operativo);
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
                $dataServicio['porcentaje_descuento'] = $servicio['porcentaje_descuento'];
                $dataServicio['descuento_unitario'] = $servicio['descuento_unitario'];
                $dataServicio['precio_neto_unitario'] = $servicio['precio_neto_unitario'];
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

        $orden->cliente = ClienteService::getOne($orden->id_cliente);
        if ($orden->cliente && $orden->cliente->id_user) {
            $orden->cliente->user = UserService::getOneById($orden->cliente->id_user);
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

        // Siempre cargar operativos asignados para cada servicio de la orden
        foreach ($orden->array_servicios as $servicio) {
            $servicio->operativos_asignados = DB::table('ordenes_servicios_operativos')
                ->join('operativos', 'ordenes_servicios_operativos.id_operativo', '=', 'operativos.id_operativo')
                ->join('especialidades', 'ordenes_servicios_operativos.id_especialidad', '=', 'especialidades.id_especialidad')
                ->leftJoin('ordenes_servicios_especialidades', function ($join) {
                $join->on('ordenes_servicios_operativos.id_orden_servicio', '=', 'ordenes_servicios_especialidades.id_orden_servicio')
                    ->on('ordenes_servicios_operativos.id_especialidad', '=', 'ordenes_servicios_especialidades.id_especialidad');
            })
                ->where('ordenes_servicios_operativos.id_orden_servicio', $servicio->id_orden_servicio)
                ->select(
                'operativos.id_operativo',
                'operativos.id_user',
                'operativos.nombre as nombre_operativo',
                'especialidades.nombre as nombre_especialidad',
                'especialidades.nivel',
                'ordenes_servicios_operativos.es_jefe',
                'ordenes_servicios_operativos.fecha_inicio',
                'ordenes_servicios_operativos.fecha_fin',
                DB::raw('COALESCE(ordenes_servicios_especialidades.horas_hombre * ordenes_servicios_especialidades.tarifa_hora, 0) as ingreso')
            )
                ->get();

            // Cargar equipos
            $servicio->equipos_asignados = DB::table('ordenes_servicios_equipos')
                ->join('equipos', 'ordenes_servicios_equipos.id_equipo', '=', 'equipos.id_equipo')
                ->join('tipos_equipos', 'equipos.id_tipo_equipo', '=', 'tipos_equipos.id_tipo_equipo')
                ->where('ordenes_servicios_equipos.id_orden_servicio', $servicio->id_orden_servicio)
                ->select(
                'equipos.id_equipo',
                'tipos_equipos.nombre as nombre_equipo',
                'equipos.modelo',
                'ordenes_servicios_equipos.fecha_inicio',
                'ordenes_servicios_equipos.fecha_fin'
            )
                ->get();

            // Cargar materiales
            $servicio->materiales_asignados = DB::table('ordenes_servicios_materiales')
                ->join('materiales', 'ordenes_servicios_materiales.id_material', '=', 'materiales.id_material')
                ->where('ordenes_servicios_materiales.id_orden_servicio', $servicio->id_orden_servicio)
                ->select(
                'materiales.id_material',
                'materiales.nombre as nombre_material',
                'ordenes_servicios_materiales.cantidad as cantidad_usada',
                'ordenes_servicios_materiales.precio_unitario'
            )
                ->get();
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

        if ($request->has('calificacion')) {
            // sumar esa calificacion al campo reputacion de cada uno de los empleados que participaron en esa orden
            $orden->array_servicios = OrdenServicioService::getOneByOrden($id);
            foreach ($orden->array_servicios as $servicio) {
                $servicio->operativos_asignados = DB::table('ordenes_servicios_operativos')
                    ->join('operativos', 'ordenes_servicios_operativos.id_operativo', '=', 'operativos.id_operativo')
                    ->where('ordenes_servicios_operativos.id_orden_servicio', $servicio->id_orden_servicio)
                    ->select(
                    'operativos.id_operativo'
                )
                    ->get();

                foreach ($servicio->operativos_asignados as $operativoAux) {
                    $operativo = OperativoService::getOne($operativoAux->id_operativo);
                    $operativo->reputacion += $request->calificacion;
                    $operativo->save();
                }
            }
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
        $orden->fecha_validacion = date('Y-m-d H:i:s');
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
        $orden->fecha_validacion = date('Y-m-d H:i:s');
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

    public function completarOrden(Request $request, $id)
    {
        $request->validate([
            'fecha_fin_real' => 'required|date',
        ]);

        $data = $request->all();

        $orden = OrdenService::getOne($id);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }
        $orden->estado = 'Completada';
        $orden->fecha_fin_real = $data['fecha_fin_real'];
        $orden->save();

        //obtener id_cliente para enviar correo notificando la accion
        $cliente = ClienteService::getOne($orden->id_cliente);
        $id_user_cliente = $cliente->id_user;
        $user = User::where('id', $id_user_cliente)->first();

        MailerService::enviarCorreo([
            'to' => [$user->email],
            'cc' => [],
            'bcc' => [],
        ], 'Orden completada', 'emails.completacion_orden', ['nombre' => $user->name, 'id_orden' => $orden->id_orden]);

        //grabar registro en la tabla notificaciones
        $notificacion = NotificacionService::store([
            'id_user' => $user->id,
            'asunto' => 'Orden completada',
            'fecha_envio' => date('Y-m-d H:i:s'),
        ]);

        return $this->successResponse($orden, 'Orden completada correctamente');
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
            $servicio->operadores_asignados = OrdenServicioOperativoService::getOneByServicio($servicio->id_orden_servicio);
            $servicio->equipos_asignados = OrdenServicioEquipoService::getOneByServicio($servicio->id_orden_servicio);
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
            if (isset($payload['findes_laborables'])) {
                $orden->findes_laborables = $payload['findes_laborables'];
            }
            $orden->save();

            foreach ($servicios as $srvData) {
                $id_orden_servicio = $srvData['id_orden_servicio'];

                // Eliminar asignaciones previas antes de insertar las nuevas (soporte para Reasignación)
                DB::table('ordenes_servicios_operativos')->where('id_orden_servicio', $id_orden_servicio)->delete();
                DB::table('ordenes_servicios_equipos')->where('id_orden_servicio', $id_orden_servicio)->delete();

                $ordenServicio = OrdenServicio::find($id_orden_servicio);

                // 2. Actualizar fechas del servicio (ya calculadas en el frontend)
                $ordenServicio->fecha_inicio = $srvData['fecha_inicio'] ?? null;
                $ordenServicio->fecha_fin = $srvData['fecha_fin'] ?? null;
                $ordenServicio->save();

                // 3. Crear registros de operativos
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

            //notificar a cada uno de los operativos sobre la asignacion
            foreach ($servicios as $srvData) {
                foreach ($srvData['operadores'] as $opData) {
                    $operativo = Operativo::find($opData['id_operativo']);
                    $user = User::find($operativo->id_user);
                    MailerService::enviarCorreo([
                        'to' => [$user->email],
                        'cc' => [],
                        'bcc' => [],
                    ], 'Asignación de orden', 'emails.asignacion_orden', ['nombre' => $user->name, 'id_orden' => $orden->id_orden, 'fecha_inicio' => $orden->fecha_inicio, 'fecha_fin' => $orden->fecha_fin]);

                    //grabar registro en la tabla notificaciones
                    $notificacion = NotificacionService::store([
                        'id_user' => $user->id,
                        'asunto' => 'Orden asignada',
                        'fecha_envio' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            return $this->successResponse($orden, 'Asignación guardada con éxito.');
        }
        catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }
    public function ponerEnEjecucion(Request $request, $id)
    {
        $user = $request->user();
        $admin = DB::table('admins')->where('id_user', $user->id)->first();
        $id_admin = $admin ? $admin->id_admin : null;
        $confirmConflicts = $request->has('confirm_conflicts') && $request->confirm_conflicts == true;

        DB::beginTransaction();
        try {
            $orden = Orden::find($id);
            if (!$orden) {
                return $this->errorResponse('Orden no encontrada', 404);
            }

            if ($orden->estado !== 'En espera') {
                return $this->errorResponse('La orden no está en espera para ser ejecutada', 400);
            }

            // 1. REPROGRAMACIÓN AUTOMÁTICA
            // El sistema empuja todo el calendario para que coincida con el momento real de inicio.
            $orden = OrdenService::reprogramarSegunEjecucion($id);

            // 2. DETECCIÓN DE CONFLICTOS (Traslapes)
            $conflictos = OrdenService::detectarConflictos($id);

            // Si hay conflictos y el administrador no ha confirmado explícitamente, devolvemos advertencia.
            if ((!empty($conflictos['operativos']) || !empty($conflictos['equipos'])) && !$confirmConflicts) {
                DB::rollback();
                return $this->successResponse([
                    'requiere_confirmacion' => true,
                    'conflictos' => $conflictos
                ], 'Se detectaron conflictos de agenda para esta reprogramación automática.');
            }

            // 3. Obtener materiales y validar stock
            $idServicios = DB::table('ordenes_servicios')
                ->where('id_orden', $id)
                ->pluck('id_orden_servicio');

            $materialesAsignados = DB::table('ordenes_servicios_materiales')
                ->whereIn('id_orden_servicio', $idServicios)
                ->get();

            // Validación de stock
            foreach ($materialesAsignados as $ma) {
                $material = MaterialService::getOne($ma->id_material);
                if ($material) {
                    if ($material->stock_actual < $ma->cantidad) {
                        return $this->errorResponse("No hay suficiente stock para el material: {$material->nombre}. (Se requieren {$ma->cantidad} y solo hay {$material->stock_actual} disponibles).", 400);
                    }
                }
            }

            // 4. Descontar materiales y registrar movimientos
            $materialesBajoStock = [];

            foreach ($materialesAsignados as $ma) {
                $resultado = MovimientoMaterialService::store([
                    'id_material' => $ma->id_material,
                    'id_admin' => $id_admin,
                    'tipo_movimiento' => 'salida',
                    'cantidad' => $ma->cantidad,
                    'motivo' => "Consumo por ejecución de Orden #{$orden->id_orden}",
                ]);

                if (!$resultado) {
                    throw new \Exception("Error al registrar movimiento para el material ID: {$ma->id_material}");
                }

                if ($resultado->alerta) {
                    if (!isset($materialesBajoStock[$resultado->alerta->id_material])) {
                        $materialesBajoStock[$resultado->alerta->id_material] = [
                            'nombre' => $resultado->alerta->nombre,
                            'stock_actual' => $resultado->alerta->stock_actual,
                            'stock_minimo' => $resultado->alerta->stock_minimo,
                        ];
                    }
                    else {
                        $materialesBajoStock[$resultado->alerta->id_material]['stock_actual'] = $resultado->alerta->stock_actual;
                    }
                }
            }

            if (!empty($materialesBajoStock)) {
                MovimientoMaterialService::notificarVariosStockBajo(array_values($materialesBajoStock));
            }

            // 5. Actualizar estado
            $orden->estado = 'En ejecucion';
            $orden->save();

            DB::commit();

            // 6. Notificar a los operativos asignados
            try {
                $operativos = DB::table('ordenes_servicios_operativos')
                    ->join('ordenes_servicios', 'ordenes_servicios_operativos.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
                    ->join('operativos', 'ordenes_servicios_operativos.id_operativo', '=', 'operativos.id_operativo')
                    ->join('users', 'operativos.id_user', '=', 'users.id')
                    ->where('ordenes_servicios.id_orden', $id)
                    ->select('users.id', 'users.name', 'users.email')
                    ->distinct()
                    ->get();

                foreach ($operativos as $opUser) {
                    MailerService::enviarCorreo(
                    ['to' => [$opUser->email]],
                        "Orden #{$orden->id_orden} en ejecución",
                        'emails.orden_en_ejecucion',
                    [
                        'nombre' => $opUser->name,
                        'id_orden' => $orden->id_orden,
                        'fecha_inicio' => \Carbon\Carbon::parse($orden->fecha_inicio)->format('d/m/Y H:i')
                    ]
                    );

                    NotificacionService::store([
                        'id_user' => $opUser->id,
                        'asunto' => "La orden #{$orden->id_orden} ha sido puesta en ejecución",
                        'fecha_envio' => now()
                    ]);
                }
            }
            catch (\Exception $e) {
            // Notificación fallida, solo loguear
            }

            return $this->successResponse($orden, 'Orden puesta en ejecución correctamente. Inventario actualizado y fechas reprogramadas.');
        }
        catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Error al poner en ejecución: ' . $e->getMessage(), 500);
        }
    }

    public function getAllAsignaciones()
    {
        $operativosAsignados = DB::table('ordenes_servicios_operativos')
            ->join('ordenes_servicios', 'ordenes_servicios_operativos.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
            ->join('ordenes', 'ordenes_servicios.id_orden', '=', 'ordenes.id_orden')
            ->whereIn('ordenes.estado', ['En espera', 'En ejecucion'])
            ->select('ordenes_servicios_operativos.*', 'ordenes_servicios.id_orden')
            ->get();

        $equiposAsignados = DB::table('ordenes_servicios_equipos')
            ->join('ordenes_servicios', 'ordenes_servicios_equipos.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
            ->join('ordenes', 'ordenes_servicios.id_orden', '=', 'ordenes.id_orden')
            ->whereIn('ordenes.estado', ['En espera', 'En ejecucion'])
            ->select('ordenes_servicios_equipos.*', 'ordenes_servicios.id_orden')
            ->get();

        return $this->successResponse([
            'operativos' => $operativosAsignados,
            'equipos' => $equiposAsignados
        ], 'Asignaciones obtenidas correctamente');
    }

    public function getOneOrdenAvances($id)
    {
        $orden = Orden::find($id);
        if (!$orden) {
            return $this->errorResponse('Orden no encontrada', 404);
        }

        $avancesOrden = AvanceOrdenService::getAllByOrden($id);

        return $this->successResponse([
            'orden' => $orden,
            'avances_orden' => $avancesOrden
        ], 'Avances de la orden obtenidos correctamente');
    }
}
