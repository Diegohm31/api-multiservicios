<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Servicio;
class ServicioService
{
    public static function getAll()
    {
        //join con tabla tipos_servicios y traerse el campo nombre de la tabla tipos_servicios con el alias nombre_tipo_servicio
        $servicios = Servicio::join('tipos_servicios', 'servicios.id_tipo_servicio', '=', 'tipos_servicios.id_tipo_servicio')
            ->select('servicios.*', 'tipos_servicios.nombre as nombre_tipo_servicio')
            ->where('servicios.is_deleted', false)
            ->get();
        return $servicios;
    }

    public static function getOne($id)
    {
        $servicio = Servicio::where('is_deleted', false)->find($id);
        return $servicio;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $data['is_deleted'] = false;
        $servicio = Servicio::create($data);

        DB::commit();
        return $servicio;
    }

    public static function update($id, $data)
    {

        $servicio = Servicio::where('is_deleted', false)->find($id);

        if (!$servicio) {
            return null;
        }

        if (!$data['servicio_tabulado'] && $servicio->servicio_tabulado) {
            //poner todos los precios y duracion en null
            $data['precio_mano_obra'] = null;
            $data['precio_materiales'] = null;
            $data['precio_tipos_equipos'] = null;
            $data['precio_general'] = null;
            $data['duracion_horas'] = null;
        }

        DB::beginTransaction();
        $servicio->update($data);
        DB::commit();
        return $servicio;
    }

    public static function delete($id)
    {
        $servicio = Servicio::where('is_deleted', false)->find($id);

        if (!$servicio) {
            return null;
        }

        DB::beginTransaction();
        //borrado logico
        $servicio->update(['is_deleted' => true]);
        DB::commit();
        return $servicio;
    }

    public static function esTabulado($id)
    {
        $servicio = Servicio::where('is_deleted', false)->find($id);
        return $servicio->servicio_tabulado;
    }

}