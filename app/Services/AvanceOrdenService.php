<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\AvanceOrden;

class AvanceOrdenService
{
    public static function getAll()
    {
        $avanceOrdenes = AvanceOrden::where('is_deleted', false)->get();
        return $avanceOrdenes;
    }

    public static function getOne($id)
    {
        $avanceOrden = AvanceOrden::where('id_avance_orden', $id)->where('is_deleted', false)->first();
        return $avanceOrden;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $data['is_deleted'] = false;
        $data['fecha_avance'] = now();
        $avanceOrden = AvanceOrden::create($data);

        DB::commit();
        return $avanceOrden;
    }

    public static function update($id, $data)
    {

        $avanceOrden = AvanceOrden::where('id_avance_orden', $id)->where('is_deleted', false)->first();

        if (!$avanceOrden) {
            return null;
        }

        DB::beginTransaction();
        $avanceOrden->update($data);
        DB::commit();
        return $avanceOrden;
    }

    public static function delete($id)
    {
        $avanceOrden = AvanceOrden::where('id_avance_orden', $id)->where('is_deleted', false)->first();

        if (!$avanceOrden) {
            return null;
        }

        DB::beginTransaction();
        //borrado logico
        $avanceOrden->is_deleted = true;
        $avanceOrden->save();
        DB::commit();
        return $avanceOrden;
    }

    public static function getAllByOrden($id)
    {
        $avanceOrdenes = AvanceOrden::where('id_orden', $id)->where('is_deleted', false)->get();
        return $avanceOrdenes;
    }
}