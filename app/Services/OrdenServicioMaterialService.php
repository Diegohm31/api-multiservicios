<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\OrdenServicioMaterial;

class OrdenServicioMaterialService
{
    public static function getAll()
    {
        $ordenServicioMateriales = OrdenServicioMaterial::get();
        return $ordenServicioMateriales;
    }

    public static function getOne($id)
    {
        $ordenServicioMaterial = OrdenServicioMaterial::find($id);
        return $ordenServicioMaterial;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $ordenServicioMaterial = OrdenServicioMaterial::create($data);

        DB::commit();
        return $ordenServicioMaterial;
    }

    public static function update($id, $data)
    {

        $ordenServicioMaterial = OrdenServicioMaterial::find($id);

        if (!$ordenServicioMaterial) {
            return null;
        }

        DB::beginTransaction();
        $ordenServicioMaterial->update($data);
        DB::commit();
        return $ordenServicioMaterial;
    }

    public static function delete($id)
    {
        $ordenServicioMaterial = OrdenServicioMaterial::find($id);

        if (!$ordenServicioMaterial) {
            return null;
        }

        DB::beginTransaction();
        $ordenServicioMaterial->delete();
        DB::commit();
        return $ordenServicioMaterial;
    }

    public static function getOneByServicio($id_orden_servicio)
    {
        //join para obtener el nombre del material
        //alias al campo cantidad para que no se repita con el campo cantidad de la tabla ordenes_servicios
        $ordenServicioMaterial = OrdenServicioMaterial::join('materiales', 'ordenes_servicios_materiales.id_material', '=', 'materiales.id_material')->select('ordenes_servicios_materiales.*', 'ordenes_servicios_materiales.cantidad as cantidad_orden_servicio_material', 'materiales.*', 'materiales.stock_actual as cantidad_material')->where('id_orden_servicio', $id_orden_servicio)->get();
        return $ordenServicioMaterial;
    }
}