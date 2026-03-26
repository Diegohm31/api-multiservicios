<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\OrdenServicioEquipo;

class OrdenServicioEquipoService
{
    public static function getAll()
    {
        $registros = DB::table('ordenes_servicios_equipos')
            ->join('ordenes_servicios', 'ordenes_servicios_equipos.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
            ->select('ordenes_servicios_equipos.*', 'ordenes_servicios.id_orden')
            ->get();
        return $registros;
    }

    public static function getOne($id)
    {
        $registro = OrdenServicioEquipo::find($id);
        return $registro;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $registro = OrdenServicioEquipo::create($data);

        DB::commit();
        return $registro;
    }

    public static function update($id, $data)
    {

        $registro = OrdenServicioEquipo::find($id);

        if (!$registro) {
            return null;
        }

        DB::beginTransaction();
        $registro->update($data);
        DB::commit();
        return $registro;
    }

    public static function delete($id)
    {
        $registro = OrdenServicioEquipo::find($id);

        if (!$registro) {
            return null;
        }

        DB::beginTransaction();
        $registro->delete();
        DB::commit();
        return $registro;
    }

    public static function getOneByServicio($id_orden_servicio)
    {
        return DB::table('ordenes_servicios_equipos')
            ->where('id_orden_servicio', $id_orden_servicio)
            ->get();
    }
}