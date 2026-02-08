<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\ServicioTipoEquipo;

class ServicioTipoEquipoService
{
    public static function getAll()
    {
        $registros = ServicioTipoEquipo::get();
        return $registros;
    }

    public static function getOne($id)
    {
        $registro = ServicioTipoEquipo::find($id);
        return $registro;
    }

    public static function getOneByServicio($id)
    {
        $registro = ServicioTipoEquipo::select('servicios_tipos_equipos.*', 'servicios_tipos_equipos.cantidad as cantidad_servicio', 'tipos_equipos.nombre', 'tipos_equipos.costo_hora')
            ->join('tipos_equipos', 'servicios_tipos_equipos.id_tipo_equipo', '=', 'tipos_equipos.id_tipo_equipo')
            ->where('servicios_tipos_equipos.id_servicio', $id)
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
        $registro = ServicioTipoEquipo::create($data);

        DB::commit();
        return $registro;
    }

    public static function delete($id)
    {
        $registro = ServicioTipoEquipo::find($id);

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
        $registros = ServicioTipoEquipo::where('id_servicio', $id)->get();

        if ($registros->isEmpty()) {
            return null;
        }

        DB::beginTransaction();
        $registros->each->delete();
        DB::commit();
        return $registros;
    }

    public static function updateByServicioAndTipoEquipo($id_servicio, $id_tipo_equipo, $data)
    {
        $registro = ServicioTipoEquipo::where('id_servicio', $id_servicio)
            ->where('id_tipo_equipo', $id_tipo_equipo)
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