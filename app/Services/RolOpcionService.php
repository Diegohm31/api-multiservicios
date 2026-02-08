<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\RolOpcion;

class RolOpcionService
{
    public static function getAll()
    {
        $registros = RolOpcion::get();
        return $registros;
    }

    public static function getOne($id)
    {
        $registro = RolOpcion::find($id);
        return $registro;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $registro = RolOpcion::create($data);

        DB::commit();
        return $registro;
    }

    public static function delete($id)
    {
        $registro = RolOpcion::find($id);

        if (!$registro) {
            return null;
        }

        DB::beginTransaction();
        $registro->delete();
        DB::commit();
        return $registro;
    }

}