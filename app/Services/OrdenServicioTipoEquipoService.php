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

}