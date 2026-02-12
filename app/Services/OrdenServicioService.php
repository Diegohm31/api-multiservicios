<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\OrdenServicio;

class OrdenServicioService
{
    public static function getAll()
    {
        $ordenes = OrdenServicio::get();
        return $ordenes;
    }

    public static function getOne($id)
    {
        $orden = OrdenServicio::find($id);
        return $orden;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $orden = OrdenServicio::create($data);

        DB::commit();
        return $orden;
    }

    public static function update($id, $data)
    {

        $orden = OrdenServicio::find($id);

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
        $orden = OrdenServicio::find($id);

        if (!$orden) {
            return null;
        }

        DB::beginTransaction();
        $orden->delete();
        DB::commit();
        return $orden;
    }

    public static function getOneByOrden($id_orden)
    {
        //join con la tabla servicios para obtener el nombre del servicio
        $servicios = DB::table('ordenes_servicios')
            ->join('servicios', 'ordenes_servicios.id_servicio', '=', 'servicios.id_servicio')
            ->where('ordenes_servicios.id_orden', $id_orden)
            ->select('ordenes_servicios.*', 'servicios.nombre', 'servicios.servicio_tabulado')
            ->get();
        return $servicios;
    }
}