<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Membresia;
use App\Services\PlanMembresiaService;
use App\Services\PlanMembresiaTipoServicioService;

class MembresiaService
{
    public static function getAll()
    {
        $membresias = Membresia::get();
        return $membresias;
    }

    public static function getOne($id)
    {
        $membresia = Membresia::find($id);
        return $membresia;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $data['estado'] = 'Pendiente';
        $membresia = Membresia::create($data);

        DB::commit();
        return $membresia;
    }

    public static function update($id, $data)
    {

        $membresia = Membresia::find($id);

        if (!$membresia) {
            return null;
        }

        DB::beginTransaction();
        $membresia->update($data);
        DB::commit();
        return $membresia;
    }

    public static function delete($id)
    {
        $membresia = Membresia::find($id);

        if (!$membresia) {
            return null;
        }

        DB::beginTransaction();
        $membresia->delete();
        DB::commit();
        return $membresia;
    }

    public static function getActiveByCliente($id_cliente)
    {
        $membresia = Membresia::where('id_cliente', $id_cliente)
            ->where('estado', 'Activa')
            ->first();

        if ($membresia) {
            $plan = PlanMembresiaService::getOne($membresia->id_plan_membresia);
            if ($plan) {
                $plan->array_tipos_servicios = PlanMembresiaTipoServicioService::getOneByPlanMembresia($plan->id_plan_membresia);
                $membresia->plan = $plan;
            }
        }
        return $membresia;
    }
}