<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\OrdenServicioOperativo;

class OrdenServicioOperativoService
{
    public static function getAll()
    {
        $registros = DB::table('ordenes_servicios_operativos')
            ->join('ordenes_servicios', 'ordenes_servicios_operativos.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
            ->select('ordenes_servicios_operativos.*', 'ordenes_servicios.id_orden')
            ->get();
        return $registros;
    }

    public static function getOne($id)
    {
        $registro = OrdenServicioOperativo::find($id);
        return $registro;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $registro = OrdenServicioOperativo::create($data);

        DB::commit();
        return $registro;
    }

    public static function update($id, $data)
    {

        $registro = OrdenServicioOperativo::find($id);

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
        $registro = OrdenServicioOperativo::find($id);

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
        return DB::table('ordenes_servicios_operativos')
            ->where('id_orden_servicio', $id_orden_servicio)
            ->get();
    }
}