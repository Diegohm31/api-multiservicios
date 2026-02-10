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

}