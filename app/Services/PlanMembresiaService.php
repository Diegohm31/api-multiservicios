<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\PlanMembresia;

class PlanMembresiaService
{
    public static function getAll()
    {
        $planes_membresias = PlanMembresia::where('is_deleted', false)->get();
        return $planes_membresias;
    }

    public static function getOne($id)
    {
        $plan_membresia = PlanMembresia::where('id_plan_membresia', $id)->where('is_deleted', false)->first();
        return $plan_membresia;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $data['is_deleted'] = false;
        $plan_membresia = PlanMembresia::create($data);

        DB::commit();
        return $plan_membresia;
    }

    public static function update($id, $data)
    {
        $plan_membresia = PlanMembresia::where('id_plan_membresia', $id)->where('is_deleted', false)->first();

        if (!$plan_membresia) {
            return null;
        }

        DB::beginTransaction();
        $plan_membresia->update($data);
        DB::commit();
        return $plan_membresia;
    }

    public static function delete($id)
    {
        $plan_membresia = PlanMembresia::where('id_plan_membresia', $id)->where('is_deleted', false)->first();

        if (!$plan_membresia) {
            return null;
        }

        DB::beginTransaction();
        // Borrado logico
        $plan_membresia->is_deleted = true;
        $plan_membresia->save();
        DB::commit();
        return $plan_membresia;
    }
}