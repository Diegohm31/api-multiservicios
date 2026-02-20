<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\PlanMembresiaTipoServicio;

class PlanMembresiaTipoServicioService
{
    public static function getAll()
    {
        $registros = PlanMembresiaTipoServicio::get();
        return $registros;
    }

    public static function getOne($id)
    {
        $registro = PlanMembresiaTipoServicio::find($id);
        return $registro;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $registro = PlanMembresiaTipoServicio::create($data);

        DB::commit();
        return $registro;
    }

    public static function update($id, $data)
    {

        $registro = PlanMembresiaTipoServicio::find($id);

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
        $registro = PlanMembresiaTipoServicio::find($id);

        if (!$registro) {
            return null;
        }

        DB::beginTransaction();
        $registro->delete();
        DB::commit();
        return $registro;
    }

    public static function getOneByPlanMembresia($id)
    {
        $registros = DB::table('planes_membresias_tipos_servicios')
            ->join('tipos_servicios', 'planes_membresias_tipos_servicios.id_tipo_servicio', '=', 'tipos_servicios.id_tipo_servicio')
            ->where('id_plan_membresia', $id)
            ->select('planes_membresias_tipos_servicios.*', 'tipos_servicios.nombre as nombre_tipo_servicio')
            ->get();
        return $registros;
    }

    public static function deleteByPlanMembresiaAndTipoServicio($idPlanMembresia, $idTipoServicio)
    {
        $registro = PlanMembresiaTipoServicio::where('id_plan_membresia', $idPlanMembresia)->where('id_tipo_servicio', $idTipoServicio)->first();

        if (!$registro) {
            return null;
        }

        DB::beginTransaction();
        $registro->delete();
        DB::commit();
        return $registro;
    }

}