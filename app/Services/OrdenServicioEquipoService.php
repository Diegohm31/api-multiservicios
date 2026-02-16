<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\OrdenServicioEquipo;

class OrdenServicioEquipoService
{
    public static function getAll()
    {
        $registros = OrdenServicioEquipo::get();
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

}