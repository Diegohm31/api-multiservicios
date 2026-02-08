<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Opcion;

class OpcionService
{
    public static function getAll()
    {
        $opciones = Opcion::get();
        return $opciones;
    }

    public static function getOne($id)
    {
        $opcion = Opcion::find($id);
        return $opcion;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $opcion = Opcion::create($data);

        DB::commit();
        return $opcion;
    }

    public static function update($id, $data)
    {

        $opcion = Opcion::find($id);

        if (!$opcion) {
            return null;
        }

        DB::beginTransaction();
        $opcion->update($data);
        DB::commit();
        return $opcion;
    }

    public static function delete($id)
    {
        $opcion = Opcion::find($id);

        if (!$opcion) {
            return null;
        }

        DB::beginTransaction();
        $opcion->delete();
        DB::commit();
        return $opcion;
    }

    public static function getMenu($id_rol)
    {
        // $opciones = DB::table('opciones')
        //     ->join('rol_opciones', 'opciones.id', '=', 'rol_opciones.id_opcion')
        //     ->where('rol_opciones.id_rol', $id_rol)
        //     ->get();

        // sql raw
        $opciones = DB::select("SELECT * 
        FROM opciones 
        INNER JOIN roles_opciones ON opciones.id_opcion = roles_opciones.id_opcion 
        WHERE roles_opciones.id_rol = ? AND opciones.es_categoria = true
        order by opciones.nombre", [$id_rol]);

        return $opciones;
    }

    public static function getMenuByPadre($id_rol, $id_padre)
    {
        // $opciones = DB::table('opciones')
        //     ->join('rol_opciones', 'opciones.id', '=', 'rol_opciones.id_opcion')
        //     ->where('rol_opciones.id_rol', $id_rol)
        //     ->get();

        // sql raw
        $opciones = DB::select("SELECT * 
        FROM opciones 
        INNER JOIN roles_opciones ON opciones.id_opcion = roles_opciones.id_opcion 
        WHERE roles_opciones.id_rol = ? AND opciones.id_padre = ?", [$id_rol, $id_padre]);

        return $opciones;
    }

}