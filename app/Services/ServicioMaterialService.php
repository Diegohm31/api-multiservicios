<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\ServicioMaterial;

class ServicioMaterialService
{
    public static function getAll()
    {
        $registros = ServicioMaterial::get();
        return $registros;
    }

    public static function getOne($id)
    {
        $registro = ServicioMaterial::find($id);
        return $registro;
    }

    public static function getOneByServicio($id)
    {
        $registro = ServicioMaterial::select('servicios_materiales.*', 'servicios_materiales.cantidad as cantidad_servicio', 'materiales.nombre', 'materiales.unidad_medida', 'materiales.precio_unitario')
            ->join('materiales', 'servicios_materiales.id_material', '=', 'materiales.id_material')
            ->where('servicios_materiales.id_servicio', $id)
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
        $registro = ServicioMaterial::create($data);

        DB::commit();
        return $registro;
    }

    public static function delete($id)
    {
        $registro = ServicioMaterial::find($id);

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
        $registros = ServicioMaterial::where('id_servicio', $id)->get();

        if ($registros->isEmpty()) {
            return null;
        }

        DB::beginTransaction();
        $registros->each->delete();
        DB::commit();
        return $registros;
    }

    public static function updateByServicioAndMaterial($id_servicio, $id_material, $data)
    {
        $registro = ServicioMaterial::where('id_servicio', $id_servicio)
            ->where('id_material', $id_material)
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