<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\TipoServicio;

class TipoServicioService
{
    public static function getAll()
    {
        $tipos_servicios = TipoServicio::get();
        return $tipos_servicios;
    }

    public static function getOne($id)
    {
        $tipo_servicio = TipoServicio::find($id);
        return $tipo_servicio;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $tipo_servicio = TipoServicio::create($data);

        DB::commit();
        return $tipo_servicio;
    }

    public static function update($id, $data)
    {

        $tipo_servicio = TipoServicio::find($id);

        if (!$tipo_servicio) {
            return null;
        }

        DB::beginTransaction();
        $tipo_servicio->update($data);
        DB::commit();
        return $tipo_servicio;
    }

    public static function delete($id)
    {
        $tipo_servicio = TipoServicio::find($id);

        if (!$tipo_servicio) {
            return null;
        }

        DB::beginTransaction();
        $tipo_servicio->delete();
        DB::commit();
        return $tipo_servicio;
    }

}