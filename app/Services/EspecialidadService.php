<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Especialidad;
use App\Models\ServicioEspecialidad;
use App\Models\Servicio;

class EspecialidadService
{
    public static function getAll()
    {
        $especialidades = Especialidad::where('is_deleted', false)->get();
        return $especialidades;
    }

    public static function getOne($id)
    {
        $especialidad = Especialidad::where('is_deleted', false)->find($id);
        return $especialidad;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $data['is_deleted'] = false;
        $data['cantidad'] = 0;
        $especialidad = Especialidad::create($data);

        DB::commit();
        return $especialidad;
    }

    public static function update($id, $data)
    {

        $especialidad = Especialidad::where('is_deleted', false)->find($id);

        if (!$especialidad) {
            return null;
        }


        if (isset($data['cantidad'])) {
            $especialidad->cantidad += $data['cantidad'];
            $especialidad->save();
            unset($data['cantidad']);
        }

        $tarifa_hora_actual = $especialidad->tarifa_hora;

        DB::beginTransaction();
        $especialidad->update($data);

        // Solo ejecutar si realmente cambió la tarifa_hora y viene en la data
        if (isset($data['tarifa_hora']) && $data['tarifa_hora'] != $tarifa_hora_actual) {
            $diferencia_unitaria = $data['tarifa_hora'] - $tarifa_hora_actual;

            // Traemos los registros de la tabla pivote para tener la 'cantidad' y 'horas_hombre'
            $relaciones = ServicioEspecialidad::where('id_especialidad', $id)->get();

            foreach ($relaciones as $relacion) {
                $servicio = Servicio::find($relacion->id_servicio);

                if ($servicio) {
                    // Calculamos el ajuste real: diferencia * horas_hombre * cantidad
                    $ajuste_total = $diferencia_unitaria * $relacion->horas_hombre * $relacion->cantidad;

                    $servicio->precio_mano_obra += $ajuste_total;
                    $servicio->precio_general += $ajuste_total;

                    $servicio->save();
                }
            }
        }

        DB::commit();
        return $especialidad;
    }

    public static function delete($id)
    {
        $especialidad = Especialidad::where('is_deleted', false)->find($id);

        if (!$especialidad) {
            return null;
        }

        if (!self::sePuedeEliminar($id)) {
            return false; // Retornamos falso si falla validación
        }

        DB::beginTransaction();
        //borrado logico
        $especialidad->update(['is_deleted' => true]);
        DB::commit();
        return $especialidad;
    }

    public static function sePuedeEliminar($id_especialidad)
    {
        // 1. Verificar si está en órdenes activas
        $enOrdenesActivas = DB::table('ordenes_servicios_especialidades')
            ->join('ordenes_servicios', 'ordenes_servicios_especialidades.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
            ->join('ordenes', 'ordenes_servicios.id_orden', '=', 'ordenes.id_orden')
            ->where('ordenes_servicios_especialidades.id_especialidad', $id_especialidad)
            ->whereIn('ordenes.estado', ['En espera', 'En ejecucion'])
            ->exists();

        if ($enOrdenesActivas) {
            return false;
        }

        // 2. Verificar si está siendo usada por algún servicio catalogado como tabulado
        $enServiciosTabulados = DB::table('servicios_especialidades')
            ->join('servicios', 'servicios_especialidades.id_servicio', '=', 'servicios.id_servicio')
            ->where('servicios_especialidades.id_especialidad', $id_especialidad)
            ->where('servicios.servicio_tabulado', true) // true o 1, asumiendo booleano
            ->where('servicios.is_deleted', false) // Sólo chequeamos servicios activos
            ->exists();

        if ($enServiciosTabulados) {
            return false;
        }

        return true;
    }
}