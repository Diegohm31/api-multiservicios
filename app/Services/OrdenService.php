<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Orden;

class OrdenService
{
    public static function getAll()
    {
        $ordenes = Orden::get();
        return $ordenes;
    }

    public static function getOne($id)
    {
        $orden = Orden::find($id);
        return $orden;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $orden = Orden::create($data);

        DB::commit();
        return $orden;
    }

    public static function update($id, $data)
    {

        $orden = Orden::find($id);

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
        $orden = Orden::find($id);

        if (!$orden) {
            return null;
        }

        DB::beginTransaction();
        $orden->delete();
        DB::commit();
        return $orden;
    }
}