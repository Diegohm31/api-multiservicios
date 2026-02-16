<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\OrdenServicioTipoEquipo;

class OrdenServicioTipoEquipoService
{
    public static function getAll()
    {
        $ordenServicioTipoEquipos = OrdenServicioTipoEquipo::get();
        return $ordenServicioTipoEquipos;
    }

    public static function getOne($id)
    {
        $ordenServicioTipoEquipo = OrdenServicioTipoEquipo::find($id);
        return $ordenServicioTipoEquipo;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $ordenServicioTipoEquipo = OrdenServicioTipoEquipo::create($data);

        DB::commit();
        return $ordenServicioTipoEquipo;
    }

    public static function update($id, $data)
    {

        $ordenServicioTipoEquipo = OrdenServicioTipoEquipo::find($id);

        if (!$ordenServicioTipoEquipo) {
            return null;
        }

        DB::beginTransaction();
        $ordenServicioTipoEquipo->update($data);
        DB::commit();
        return $ordenServicioTipoEquipo;
    }

    public static function delete($id)
    {
        $ordenServicioTipoEquipo = OrdenServicioTipoEquipo::find($id);

        if (!$ordenServicioTipoEquipo) {
            return null;
        }

        DB::beginTransaction();
        $ordenServicioTipoEquipo->delete();
        DB::commit();
        return $ordenServicioTipoEquipo;
    }

    public static function getOneByServicio($id_orden_servicio)
    {
        //join para obtener el nombre del tipo de equipo
        //alias al campo cantidad para que no se repita con el campo cantidad de la tabla ordenes_servicios
        $ordenServicioTipoEquipo = OrdenServicioTipoEquipo::join('tipos_equipos', 'ordenes_servicios_tipos_equipos.id_tipo_equipo', '=', 'tipos_equipos.id_tipo_equipo')->select('ordenes_servicios_tipos_equipos.*', 'ordenes_servicios_tipos_equipos.cantidad as cantidad_orden_servicio_tipo_equipo', 'tipos_equipos.*', 'tipos_equipos.cantidad as cantidad_tipo_equipo')->where('id_orden_servicio', $id_orden_servicio)->get();
        return $ordenServicioTipoEquipo;
    }
}