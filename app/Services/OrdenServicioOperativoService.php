<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\OrdenServicioOperativo;

class OrdenServicioOperativoService
{
    public static function getAll()
    {
        $registros = OrdenServicioOperativo::get();
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

}