<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\ReportePago;

class ReportePagoService
{
    public static function getAll()
    {
        //inner join con la tabla clientes y left join con la tabla admins
        $reportes_pagos = DB::table('reportes_pagos')
            ->join('clientes', 'reportes_pagos.id_cliente', '=', 'clientes.id_cliente')
            ->leftJoin('admins', 'reportes_pagos.id_admin', '=', 'admins.id_admin')
            ->leftJoin('planes_membresias', 'reportes_pagos.id_plan_membresia', '=', 'planes_membresias.id_plan_membresia')
            ->select('reportes_pagos.*', 'clientes.nombre as cliente_nombre', 'clientes.cedula as cliente_cedula', 'admins.nombre as admin_nombre', 'planes_membresias.nombre as plan_membresia_nombre')
            ->get();
        return $reportes_pagos;
    }

    public static function getOne($id)
    {
        $reporte_pago = ReportePago::find($id);
        return $reporte_pago;
    }

    public static function getOneAllInfo($id)
    {
        //inner join con la tabla clientes y left join con la tabla admins
        $reporte_pago = DB::table('reportes_pagos')
            ->join('clientes', 'reportes_pagos.id_cliente', '=', 'clientes.id_cliente')
            ->leftJoin('admins', 'reportes_pagos.id_admin', '=', 'admins.id_admin')
            ->leftJoin('planes_membresias', 'reportes_pagos.id_plan_membresia', '=', 'planes_membresias.id_plan_membresia')
            ->select('reportes_pagos.*', 'clientes.nombre as cliente_nombre', 'clientes.cedula as cliente_cedula', 'admins.nombre as admin_nombre', 'planes_membresias.nombre as plan_membresia_nombre')
            ->where('reportes_pagos.id_reporte_pago', $id)
            ->first();
        return $reporte_pago;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $data['estado'] = 'Pendiente';
        $data['fecha_emision'] = date('Y-m-d H:i:s');
        $reporte_pago = ReportePago::create($data);

        DB::commit();
        return $reporte_pago;
    }

    public static function update($id, $data)
    {

        $reporte_pago = ReportePago::find($id);

        if (!$reporte_pago) {
            return null;
        }

        DB::beginTransaction();
        $reporte_pago->update($data);
        DB::commit();
        return $reporte_pago;
    }

    public static function delete($id)
    {
        $reporte_pago = ReportePago::find($id);

        if (!$reporte_pago) {
            return null;
        }

        DB::beginTransaction();
        $reporte_pago->delete();
        DB::commit();
        return $reporte_pago;
    }

    public static function getOneByOrden($id_orden)
    {
        $reporte_pago = ReportePago::where('id_orden', $id_orden)->first();
        return $reporte_pago;
    }

    public static function getOneByPlanMembresia($id_plan_membresia)
    {
        $reporte_pago = ReportePago::where('id_plan_membresia', $id_plan_membresia)->first();
        return $reporte_pago;
    }
}