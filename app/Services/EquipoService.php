<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Equipo;
use App\Models\TipoEquipo;

class EquipoService
{
    public static function getAll()
    {
        $equipos = Equipo::where('is_deleted', false)->get();
        return $equipos;
    }

    public static function getOne($id)
    {
        $equipo = Equipo::where('is_deleted', false)->find($id);
        return $equipo;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $data['is_deleted'] = false;
        $data['disponible'] = true;
        $equipo = Equipo::create($data);

        $tipo_equipo = TipoEquipo::find($equipo->id_tipo_equipo);
        $tipo_equipo->cantidad = $tipo_equipo->cantidad + 1;
        $tipo_equipo->save();

        DB::commit();
        return $equipo;
    }

    public static function update($id, $data)
    {

        $equipo = Equipo::where('is_deleted', false)->find($id);

        if (!$equipo) {
            return null;
        }

        DB::beginTransaction();
        $equipo->update($data);

        if ($data['disponible'] == false) {
            $tipo_equipo = TipoEquipo::find($equipo->id_tipo_equipo);
            $tipo_equipo->cantidad = $tipo_equipo->cantidad - 1;
            $tipo_equipo->save();
        }

        DB::commit();
        return $equipo;
    }

    public static function delete($id)
    {
        $equipo = Equipo::where('is_deleted', false)->find($id);

        if (!$equipo) {
            return null;
        }

        DB::beginTransaction();
        //$equipo->delete();
        $equipo->is_deleted = true;
        $equipo->save();

        $tipo_equipo = TipoEquipo::find($equipo->id_tipo_equipo);
        $tipo_equipo->cantidad = $tipo_equipo->cantidad - 1;
        $tipo_equipo->save();

        DB::commit();
        return $equipo;
    }

}