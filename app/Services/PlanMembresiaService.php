<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\PlanMembresia;

class PlanMembresiaService
{
    public static function getAll()
    {
        $planes_membresias = PlanMembresia::get();
        return $planes_membresias;
    }

    public static function getOne($id)
    {
        $plan_membresia = PlanMembresia::find($id);
        return $plan_membresia;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $plan_membresia = PlanMembresia::create($data);

        DB::commit();
        return $plan_membresia;
    }

    public static function update($id, $data)
    {

        $plan_membresia = PlanMembresia::find($id);

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
        $plan_membresia = PlanMembresia::find($id);

        if (!$plan_membresia) {
            return null;
        }

        DB::beginTransaction();
        $plan_membresia->delete();
        DB::commit();
        return $plan_membresia;
    }
}