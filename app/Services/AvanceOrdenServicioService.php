<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\AvanceOrdenServicio;

class AvanceOrdenServicioService
{
    public static function recalcularAvanceGlobalOrden($id_orden_servicio)
    {
        // Obtener el ID de la orden
        $id_orden = DB::table('ordenes_servicios')->where('id_orden_servicio', $id_orden_servicio)->value('id_orden');
        
        if (!$id_orden) return;

        // Contar el numero de servicios
        $totalServices = DB::table('ordenes_servicios')->where('id_orden', $id_orden)->count();
        
        if ($totalServices > 0) {
            $totalAdvances = DB::table('avances_ordenes_servicios')
                ->join('ordenes_servicios', 'avances_ordenes_servicios.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
                ->where('ordenes_servicios.id_orden', $id_orden)
                ->where('avances_ordenes_servicios.is_deleted', false)
                ->sum('avances_ordenes_servicios.porcentaje_avance');
            
            $global = $totalAdvances / $totalServices;
            
            DB::table('ordenes')->where('id_orden', $id_orden)->update(['porcentaje_avance_global' => $global]);
        }
    }

    public static function getAll()
    {
        return AvanceOrdenServicio::where('is_deleted', false)->get();
    }

    public static function getOne($id)
    {
        return AvanceOrdenServicio::where('id_avance_orden_servicio', $id)->where('is_deleted', false)->first();
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $data['is_deleted'] = false;
        $data['fecha_avance'] = now();
        $avanceOrden = AvanceOrdenServicio::create($data);

        self::recalcularAvanceGlobalOrden($data['id_orden_servicio']);

        DB::commit();
        return $avanceOrden;
    }

    public static function update($id, $data)
    {
        $avanceOrden = AvanceOrdenServicio::where('id_avance_orden_servicio', $id)->where('is_deleted', false)->first();

        if (!$avanceOrden) {
            return null;
        }

        DB::beginTransaction();
        $avanceOrden->update($data);
        
        self::recalcularAvanceGlobalOrden($avanceOrden->id_orden_servicio);

        DB::commit();
        return $avanceOrden;
    }

    public static function delete($id)
    {
        $avanceOrden = AvanceOrdenServicio::where('id_avance_orden_servicio', $id)->where('is_deleted', false)->first();

        if (!$avanceOrden) {
            return null;
        }

        DB::beginTransaction();
        //borrado logico
        $avanceOrden->is_deleted = true;
        $avanceOrden->save();
        
        self::recalcularAvanceGlobalOrden($avanceOrden->id_orden_servicio);

        DB::commit();
        return $avanceOrden;
    }

    public static function getAllByOrden($id_orden)
    {
        $avanceOrdenes = DB::table('avances_ordenes_servicios')
            ->join('operativos', 'avances_ordenes_servicios.id_operativo', '=', 'operativos.id_operativo')
            ->join('ordenes_servicios', 'avances_ordenes_servicios.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
            ->where('ordenes_servicios.id_orden', $id_orden)
            ->where('avances_ordenes_servicios.is_deleted', false)
            ->select('avances_ordenes_servicios.*', 'operativos.nombre as nombre_operativo')
            ->orderBy('avances_ordenes_servicios.fecha_avance', 'asc')
            ->get();
        return $avanceOrdenes;
    }
}
