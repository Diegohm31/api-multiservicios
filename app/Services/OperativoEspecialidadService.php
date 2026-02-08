<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\OperativoEspecialidad;

class OperativoEspecialidadService
{
    public static function getAll()
    {
        $registros = OperativoEspecialidad::get();
        return $registros;
    }

    public static function getOne($id)
    {
        $registro = OperativoEspecialidad::find($id);
        return $registro;
    }

    public static function getOnebyOperativo($id)
    {
        //hacer join con la tabla especialidades y luego filtrar los registros obtenidos por id_operativo, obteniendo una coleccion

        $registro = OperativoEspecialidad::join('especialidades', 'operativos_especialidades.id_especialidad', '=', 'especialidades.id_especialidad')
            ->where('operativos_especialidades.id_operativo', $id)
            ->get();

        // $registro = DB::select("SELECT * 
        // FROM operativos_especialidades 
        // inner join especialidades on operativos_especialidades.id_especialidad = especialidades.id_especialidad 
        // WHERE operativos_especialidades.id_operativo = ?", [$id]);
        return $registro;
    }

    public static function getOneByEspecialidad($id)
    {
        $registro = OperativoEspecialidad::join('operativos', 'operativos_especialidades.id_operativo', '=', 'operativos.id_operativo')
            ->where('operativos_especialidades.id_especialidad', $id)
            ->get();

        // $registro = DB::select("SELECT * 
        // FROM operativos_especialidades 
        // inner join especialidades on operativos_especialidades.id_especialidad = especialidades.id_especialidad 
        // WHERE operativos_especialidades.id_especialidad = ?", [$id]);
        return $registro;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $registro = OperativoEspecialidad::create($data);

        DB::commit();
        return $registro;
    }

    public static function delete($id)
    {
        $registro = OperativoEspecialidad::find($id);

        if (!$registro) {
            return null;
        }

        DB::beginTransaction();
        $registro->delete();
        DB::commit();
        return $registro;
    }

    public static function deleteByEspecialidadAndOperativo($id_especialidad, $id_operativo)
    {
        $registros = OperativoEspecialidad::where('id_especialidad', $id_especialidad)->where('id_operativo', $id_operativo)->first();

        if (!$registros) {
            return null;
        }

        DB::beginTransaction();
        $registros->delete();
        DB::commit();
        return $registros;
    }

    public static function deleteByOperativo($id)
    {
        $registros = OperativoEspecialidad::where('id_operativo', $id)->get();

        if (!$registros) {
            return null;
        }

        DB::beginTransaction();
        $registros->each->delete();
        DB::commit();
        return $registros;
    }

}