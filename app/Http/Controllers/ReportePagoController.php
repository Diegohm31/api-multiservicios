<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportePagoService;
use App\Services\AdminService;
use App\Services\OrdenService;
use App\Services\ClienteService;
use App\Services\PlanMembresiaService;
use App\Services\MailerService;
use App\Services\NotificacionService;
use App\Models\User;

class ReportePagoController extends Controller
{
    public function index()
    {
        $reportes_pagos = ReportePagoService::getAll();
        return $this->successResponse(
            $reportes_pagos,
            $reportes_pagos->isEmpty() ? 'No se encontraron reportes de pagos' : 'Reportes de pagos obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {

        $user = $request->user();
        $cliente = ClienteService::getOneByUser($user->id);

        $request->validate([
            'id_orden' => 'nullable|exists:ordenes,id_orden',
            'id_plan_membresia' => 'nullable|exists:planes_membresias,id_plan_membresia',
            'monto' => 'required|numeric',
            'metodo_pago' => 'required|string|max:100',
            'num_referencia' => 'required|string|max:100',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->all();
        $data['id_cliente'] = $cliente->id_cliente;

        $reporte_pago = ReportePagoService::store($data);

        if ($reporte_pago->id_orden) {
            $orden = OrdenService::getOne($reporte_pago->id_orden);
            $orden->estado = 'Verificando Pago';
            $orden->save();
        }
        if (!$reporte_pago) {
            return $this->errorResponse('No se pudo crear el reporte de pago', 404);
        }
        return $this->successResponse($reporte_pago, 'Reporte de pago creado correctamente');
    }

    public function show(Request $request, $id)
    {
        $detalle = ($request->has('detalle')) ? $request->detalle : false;
        if ($detalle) {
            $reporte_pago = ReportePagoService::getOneAllInfo($id);
        } else {
            $reporte_pago = ReportePagoService::getOne($id);
        }

        if (!$reporte_pago) {
            return $this->errorResponse('Reporte de pago no encontrado', 404);
        }
        return $this->successResponse($reporte_pago, 'Reporte de pago obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_cliente' => 'nullable|exists:clientes,id',
            'id_admin' => 'nullable|exists:admins,id',
            'id_orden' => 'nullable|exists:ordenes,id',
            'id_plan_membresia' => 'nullable|exists:planes_membresias,id',
            'monto' => 'nullable|numeric',
            'metodo_pago' => 'nullable|string|max:100',
            'num_referencia' => 'nullable|string|max:100',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'imagePath' => 'nullable|string|max:1000',
            'estado' => 'nullable|string|max:100',
            'fecha_emision' => 'nullable|date',
            'fecha_validacion' => 'nullable|date',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_cliente') && !$request->has('id_admin') && !$request->has('id_orden') && !$request->has('id_plan_membresia') && !$request->has('monto') && !$request->has('metodo_pago') && !$request->has('num_referencia') && !$request->has('image') && !$request->has('imagePath') && !$request->has('estado') && !$request->has('fecha_emision') && !$request->has('fecha_validacion') && !$request->has('observaciones')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $reporte_pago = ReportePagoService::update($id, $data);
        if (!$reporte_pago) {
            return $this->errorResponse('Reporte de pago no encontrado', 404);
        }

        return $this->successResponse($reporte_pago, 'Reporte de pago actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $reporte_pago = ReportePagoService::delete($id);
        if (!$reporte_pago) {
            return $this->errorResponse('Reporte de pago no encontrado', 404);
        }
        return $this->successResponse($reporte_pago, 'Reporte de pago eliminado correctamente');
    }

    public function aceptarReportePago(Request $request)
    {
        $request->validate([
            'id_reporte_pago' => 'required|exists:reportes_pagos,id_reporte_pago',
            'id_orden' => 'nullable|exists:ordenes,id_orden',
            'id_plan_membresia' => 'nullable|exists:planes_membresias,id_plan_membresia',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();
        $plan_membresia = null;

        if ($data['id_orden']) {
            $id_orden = $data['id_orden'];

            $orden = OrdenService::getOne($id_orden);
            if (!$orden) {
                return $this->errorResponse('Orden no encontrada', 404);
            }
            $orden->estado = 'En espera';
            $orden->save();

            $reporte_pago = ReportePagoService::getOne($data['id_reporte_pago']);
            if (!$reporte_pago) {
                return $this->errorResponse('Reporte de pago no encontrado', 404);
            }
        }

        if ($data['id_plan_membresia']) {
            $id_plan_membresia = $data['id_plan_membresia'];
            $plan_membresia = PlanMembresiaService::getOne($id_plan_membresia);
            $reporte_pago = ReportePagoService::getOne($data['id_reporte_pago']);
            if (!$reporte_pago) {
                return $this->errorResponse('Reporte de pago no encontrado', 404);
            }
        }

        $user = $request->user();
        $admin = AdminService::getOneByUser($user->id);
        $reporte_pago->id_admin = $admin->id_admin;
        $reporte_pago->estado = 'Aceptado';
        $reporte_pago->fecha_validacion = date('Y-m-d H:i:s');
        $reporte_pago->observaciones = $data['observaciones'] ?? '';
        $reporte_pago->save();

        //obtener id_cliente para enviar correo notificando la accion
        $cliente = ClienteService::getOne($reporte_pago->id_cliente);
        $id_user_cliente = $cliente->id_user;
        $user_cliente = User::where('id', $id_user_cliente)->first();

        MailerService::enviarCorreo([
            'to' => [$user_cliente->email],
            'cc' => [],
            'bcc' => [],
        ], 'Reporte de pago aceptado', 'emails.reporte_pago_aceptado', [
            'nombre' => $user_cliente->name,
            'id_orden' => $reporte_pago->id_orden ? $reporte_pago->id_orden : '',
            'id_plan_membresia' => $reporte_pago->id_plan_membresia ? $reporte_pago->id_plan_membresia : '',
            'nombre_plan_membresia' => $plan_membresia ? $plan_membresia->nombre : '',
            'observaciones' => $data['observaciones'] ?? '',
        ]);

        //grabar registro en la tabla notificaciones
        $notificacion = NotificacionService::store([
            'id_user' => $user_cliente->id,
            'asunto' => 'Reporte de pago aceptado',
            'fecha_envio' => date('Y-m-d H:i:s'),
        ]);

        return $this->successResponse($reporte_pago, 'Reporte de pago aceptado correctamente');
    }

    public function cancelarReportePago(Request $request)
    {
        $request->validate([
            'id_reporte_pago' => 'required|exists:reportes_pagos,id_reporte_pago',
            'id_orden' => 'nullable|exists:ordenes,id_orden',
            'id_plan_membresia' => 'nullable|exists:planes_membresias,id_plan_membresia',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();
        $plan_membresia = null;

        if ($data['id_orden']) {
            $id_orden = $data['id_orden'];
            $orden = OrdenService::getOne($id_orden);
            if (!$orden) {
                return $this->errorResponse('Orden no encontrada', 404);
            }
            $orden->estado = 'Por pagar';
            $orden->save();
            $reporte_pago = ReportePagoService::getOne($data['id_reporte_pago']);
            if (!$reporte_pago) {
                return $this->errorResponse('Reporte de pago no encontrado', 404);
            }
        }

        if ($data['id_plan_membresia']) {
            $id_plan_membresia = $data['id_plan_membresia'];
            $plan_membresia = PlanMembresiaService::getOne($id_plan_membresia);
            $reporte_pago = ReportePagoService::getOne($data['id_reporte_pago']);
            if (!$reporte_pago) {
                return $this->errorResponse('Reporte de pago no encontrado', 404);
            }
        }

        $user = $request->user();
        $admin = AdminService::getOneByUser($user->id);
        $reporte_pago->id_admin = $admin->id_admin;
        $reporte_pago->estado = 'Cancelado';
        $reporte_pago->fecha_validacion = date('Y-m-d H:i:s');
        $reporte_pago->observaciones = $data['observaciones'] ?? '';
        $reporte_pago->save();

        //obtener id_cliente para enviar correo notificando la accion
        $cliente = ClienteService::getOne($reporte_pago->id_cliente);
        $id_user_cliente = $cliente->id_user;
        $user_cliente = User::where('id', $id_user_cliente)->first();

        MailerService::enviarCorreo([
            'to' => [$user_cliente->email],
            'cc' => [],
            'bcc' => [],
        ], 'Reporte de pago cancelado', 'emails.reporte_pago_cancelado', [
            'nombre' => $user_cliente->name,
            'id_orden' => $reporte_pago->id_orden ? $reporte_pago->id_orden : '',
            'id_plan_membresia' => $reporte_pago->id_plan_membresia ? $reporte_pago->id_plan_membresia : '',
            'nombre_plan_membresia' => $plan_membresia ? $plan_membresia->nombre : '',
            'observaciones' => $data['observaciones'] ?? ''
        ]);

        //grabar registro en la tabla notificaciones
        $notificacion = NotificacionService::store([
            'id_user' => $user_cliente->id,
            'asunto' => 'Reporte de pago cancelado',
            'fecha_envio' => date('Y-m-d H:i:s'),
        ]);

        return $this->successResponse($reporte_pago, 'Reporte de pago cancelado correctamente');
    }
}
