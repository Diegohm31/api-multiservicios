<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Notificacion;

class NotificacionService
{
    public static function getAll()
    {
        $notificaciones = Notificacion::get();
        return $notificaciones;
    }

    public static function getOne($id)
    {
        $notificacion = Notificacion::find($id);
        return $notificacion;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $notificacion = Notificacion::create($data);

        DB::commit();
        return $notificacion;
    }

    public static function update($id, $data)
    {

        $notificacion = Notificacion::find($id);

        if (!$notificacion) {
            return null;
        }

        DB::beginTransaction();
        $notificacion->update($data);
        DB::commit();
        return $notificacion;
    }

    public static function delete($id)
    {
        $notificacion = Notificacion::find($id);

        if (!$notificacion) {
            return null;
        }

        DB::beginTransaction();
        $notificacion->delete();
        DB::commit();
        return $notificacion;
    }

}