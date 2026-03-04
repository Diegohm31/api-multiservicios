<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Orden;

class OrdenService
{
    public static function getAll()
    {
        // Subconsulta para el porcentaje de avance
        $porcentajeSub = DB::table('avances_ordenes')
            ->selectRaw('COALESCE(SUM(porcentaje_avance), 0)')
            ->whereColumn('avances_ordenes.id_orden', 'ordenes.id_orden')
            ->where('is_deleted', false);

        //join con tabla clientes para obtener el nombre y cedula del cliente
        //join con membresias y planes para obtener el icono si tiene membresia activa
        $ordenes = DB::table('ordenes')
            ->join('clientes', 'ordenes.id_cliente', '=', 'clientes.id_cliente')
            ->leftJoin('membresias', function ($join) {
                $join->on('clientes.id_cliente', '=', 'membresias.id_cliente')
                    ->where('membresias.estado', '=', 'Activa');
            })
            ->leftJoin('planes_membresias', 'membresias.id_plan_membresia', '=', 'planes_membresias.id_plan_membresia')
            ->select('ordenes.*', 'clientes.nombre', 'clientes.cedula', 'planes_membresias.imagePath as plan_image_path', 'planes_membresias.nombre as active_plan_nombre')
            ->selectSub($porcentajeSub, 'porcentaje_avance')
            ->orderBy('ordenes.id_orden', 'asc')
            ->get();
        return $ordenes;
    }

    public static function getOne($id)
    {
        $orden = Orden::find($id);
        return $orden;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $orden = Orden::create($data);

        DB::commit();
        return $orden;
    }

    public static function update($id, $data)
    {

        $orden = Orden::find($id);

        if (!$orden) {
            return null;
        }

        DB::beginTransaction();
        $orden->update($data);
        DB::commit();
        return $orden;
    }

    public static function delete($id)
    {
        $orden = Orden::find($id);

        if (!$orden) {
            return null;
        }

        DB::beginTransaction();
        $orden->delete();
        DB::commit();
        return $orden;
    }

    public static function getOrdenesByCliente($id_cliente)
    {
        $porcentajeSub = DB::table('avances_ordenes')
            ->selectRaw('COALESCE(SUM(porcentaje_avance), 0)')
            ->whereColumn('avances_ordenes.id_orden', 'ordenes.id_orden')
            ->where('is_deleted', false);

        $ordenes = Orden::where('id_cliente', $id_cliente)
            ->select('*')
            ->selectSub($porcentajeSub, 'porcentaje_avance')
            ->orderBy('ordenes.id_orden', 'asc')
            ->get();
        return $ordenes;
    }

    public static function getOrdenesByOperativo($id_operativo)
    {
        $porcentajeSub = DB::table('avances_ordenes')
            ->selectRaw('COALESCE(SUM(porcentaje_avance), 0)')
            ->whereColumn('avances_ordenes.id_orden', 'ordenes.id_orden')
            ->where('is_deleted', false);

        $ordenes = DB::table('ordenes')
            ->join('clientes', 'ordenes.id_cliente', '=', 'clientes.id_cliente')
            ->leftJoin('membresias', function ($join) {
                $join->on('clientes.id_cliente', '=', 'membresias.id_cliente')
                    ->where('membresias.estado', '=', 'Activa');
            })
            ->leftJoin('planes_membresias', 'membresias.id_plan_membresia', '=', 'planes_membresias.id_plan_membresia')
            ->join('ordenes_servicios', 'ordenes.id_orden', '=', 'ordenes_servicios.id_orden')
            ->join('ordenes_servicios_operativos', 'ordenes_servicios.id_orden_servicio', '=', 'ordenes_servicios_operativos.id_orden_servicio')
            ->where('ordenes_servicios_operativos.id_operativo', $id_operativo)
            ->whereIn('ordenes.estado', ['En espera', 'En ejecucion', 'Completada'])
            ->select('ordenes.*', 'clientes.nombre', 'clientes.cedula', 'planes_membresias.imagePath as plan_image_path', 'planes_membresias.nombre as active_plan_nombre')
            ->selectSub($porcentajeSub, 'porcentaje_avance')
            ->orderBy('ordenes.id_orden', 'asc')
            ->distinct()
            ->get();
        return $ordenes;
    }
}