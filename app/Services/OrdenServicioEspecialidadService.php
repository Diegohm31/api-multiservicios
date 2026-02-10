<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\OrdenServicioEspecialidad;

class OrdenServicioEspecialidadService
{
    public static function getAll()
    {
        $ordenServicioEspecialidades = OrdenServicioEspecialidad::get();
        return $ordenServicioEspecialidades;
    }

    public static function getOne($id)
    {
        $ordenServicioEspecialidad = OrdenServicioEspecialidad::find($id);
        return $ordenServicioEspecialidad;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $ordenServicioEspecialidad = OrdenServicioEspecialidad::create($data);

        DB::commit();
        return $ordenServicioEspecialidad;
    }

    public static function update($id, $data)
    {

        $ordenServicioEspecialidad = OrdenServicioEspecialidad::find($id);

        if (!$ordenServicioEspecialidad) {
            return null;
        }

        DB::beginTransaction();
        $ordenServicioEspecialidad->update($data);
        DB::commit();
        return $ordenServicioEspecialidad;
    }

    public static function delete($id)
    {
        $ordenServicioEspecialidad = OrdenServicioEspecialidad::find($id);

        if (!$ordenServicioEspecialidad) {
            return null;
        }

        DB::beginTransaction();
        $ordenServicioEspecialidad->delete();
        DB::commit();
        return $ordenServicioEspecialidad;
    }

}