<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\ServicioEspecialidad;

class ServicioEspecialidadService
{
    public static function getAll()
    {
        $registros = ServicioEspecialidad::get();
        return $registros;
    }

    public static function getOne($id)
    {
        $registro = ServicioEspecialidad::find($id);
        return $registro;
    }

    public static function getOneByServicio($id)
    {
        $registro = ServicioEspecialidad::select('servicios_especialidades.*', 'servicios_especialidades.cantidad as cantidad_servicio', 'especialidades.nombre', 'especialidades.tarifa_hora')
            ->join('especialidades', 'servicios_especialidades.id_especialidad', '=', 'especialidades.id_especialidad')
            ->where('servicios_especialidades.id_servicio', $id)
            ->get();

        return $registro;
    }

    // public static function getOneByEspecialidad($id)
    // {
    //     $registro = OperativoEspecialidad::join('operativos', 'operativos_especialidades.id_operativo', '=', 'operativos.id_operativo')
    //         ->where('operativos_especialidades.id_especialidad', $id)
    //         ->get();

    //     // $registro = DB::select("SELECT * 
    //     // FROM operativos_especialidades 
    //     // inner join especialidades on operativos_especialidades.id_especialidad = especialidades.id_especialidad 
    //     // WHERE operativos_especialidades.id_especialidad = ?", [$id]);
    //     return $registro;
    // }

    public static function store($data)
    {
        DB::beginTransaction();
        $registro = ServicioEspecialidad::create($data);

        DB::commit();
        return $registro;
    }

    public static function delete($id)
    {
        $registro = ServicioEspecialidad::find($id);

        if (!$registro) {
            return null;
        }

        DB::beginTransaction();
        $registro->delete();
        DB::commit();
        return $registro;
    }

    // public static function deleteByEspecialidad($id)
    // {
    //     $registros = OperativoEspecialidad::where('id_especialidad', $id)->get();

    //     if (!$registros) {
    //         return null;
    //     }

    //     DB::beginTransaction();
    //     $registros->each->delete();
    //     DB::commit();
    //     return $registros;
    // }

    // public static function deleteByOperativo($id)
    // {
    //     $registros = OperativoEspecialidad::where('id_operativo', $id)->get();

    //     if (!$registros) {
    //         return null;
    //     }

    //     DB::beginTransaction();
    //     $registros->each->delete();
    //     DB::commit();
    //     return $registros;
    // }

    public static function deleteByServicio($id)
    {
        $registros = ServicioEspecialidad::where('id_servicio', $id)->get();

        if ($registros->isEmpty()) {
            return null;
        }

        DB::beginTransaction();
        $registros->each->delete();
        DB::commit();
        return $registros;
    }

    public static function updateByServicioAndEspecialidad($id_servicio, $id_especialidad, $data)
    {
        $registro = ServicioEspecialidad::where('id_servicio', $id_servicio)
            ->where('id_especialidad', $id_especialidad)
            ->first();

        if (!$registro) {
            return null;
        }

        DB::beginTransaction();
        $registro->update($data);
        DB::commit();
        return $registro;
    }
}