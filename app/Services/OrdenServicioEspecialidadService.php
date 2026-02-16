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

    public static function getOneByServicio($id_orden_servicio)
    {
        //join para obtener el nombre de la especialidad
        //alias al campo cantidad para que no se repita con el campo cantidad de la tabla ordenes_servicios
        $ordenServicioEspecialidad = OrdenServicioEspecialidad::join('especialidades', 'ordenes_servicios_especialidades.id_especialidad', '=', 'especialidades.id_especialidad')->select('ordenes_servicios_especialidades.*', 'ordenes_servicios_especialidades.cantidad as cantidad_orden_servicio_especialidad', 'especialidades.*', 'especialidades.cantidad as cantidad_especialidad')->where('id_orden_servicio', $id_orden_servicio)->get();
        return $ordenServicioEspecialidad;
    }
}