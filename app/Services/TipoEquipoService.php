<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\TipoEquipo;
use App\Models\ServicioTipoEquipo;
use App\Models\Servicio;

class TipoEquipoService
{
    public static function getAll()
    {
        $tipos_equipos = TipoEquipo::get();
        return $tipos_equipos;
    }

    public static function getOne($id)
    {
        $tipo_equipo = TipoEquipo::find($id);
        return $tipo_equipo;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $data['cantidad'] = 0;
        $tipo_equipo = TipoEquipo::create($data);

        DB::commit();
        return $tipo_equipo;
    }

    public static function update($id, $data)
    {

        $tipo_equipo = TipoEquipo::find($id);

        $costo_hora_actual = $tipo_equipo->costo_hora;

        if (!$tipo_equipo) {
            return null;
        }

        DB::beginTransaction();
        $tipo_equipo->update($data);

        // Solo ejecutar si realmente cambió el costo_hora y viene en la data
        if (isset($data['costo_hora']) && $data['costo_hora'] != $costo_hora_actual) {
            $diferencia_unitaria = $data['costo_hora'] - $costo_hora_actual;

            // Traemos los registros de la tabla pivote para tener la 'cantidad' y 'horas_uso'
            $relaciones = ServicioTipoEquipo::where('id_tipo_equipo', $id)->get();

            foreach ($relaciones as $relacion) {
                $servicio = Servicio::find($relacion->id_servicio);

                if ($servicio) {
                    // Calculamos el ajuste real: diferencia * horas_uso * cantidad
                    $ajuste_total = $diferencia_unitaria * $relacion->horas_uso * $relacion->cantidad;

                    $servicio->precio_tipos_equipos += $ajuste_total;
                    $servicio->precio_general += $ajuste_total;

                    $servicio->save();
                }
            }
        }

        DB::commit();
        return $tipo_equipo;
    }

    public static function delete($id)
    {
        $tipo_equipo = TipoEquipo::find($id);

        if (!$tipo_equipo) {
            return null;
        }

        if (!self::sePuedeEliminar($id)) {
            return false;
        }

        DB::beginTransaction();
        $tipo_equipo->delete();
        DB::commit();
        return $tipo_equipo;
    }

    public static function sePuedeEliminar($id_tipo_equipo)
    {
        // 1. Verificar si está en órdenes activas
        $enOrdenesActivas = DB::table('ordenes_servicios_tipos_equipos')
            ->join('ordenes_servicios', 'ordenes_servicios_tipos_equipos.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
            ->join('ordenes', 'ordenes_servicios.id_orden', '=', 'ordenes.id_orden')
            ->where('ordenes_servicios_tipos_equipos.id_tipo_equipo', $id_tipo_equipo)
            ->whereNotIn('ordenes.estado', ['Cancelada', 'Completada'])
            ->exists();

        if ($enOrdenesActivas) {
            return false;
        }

        // 2. Verificar si está siendo usada por algún servicio catalogado como tabulado
        $enServiciosTabulados = DB::table('servicios_tipos_equipos')
            ->join('servicios', 'servicios_tipos_equipos.id_servicio', '=', 'servicios.id_servicio')
            ->where('servicios_tipos_equipos.id_tipo_equipo', $id_tipo_equipo)
            ->where('servicios.servicio_tabulado', true)
            ->where('servicios.is_deleted', false)
            ->exists();

        if ($enServiciosTabulados) {
            return false;
        }

        // 3. Verificar si tiene equipos asociados que no estén borrados lógicamente
        $tieneEquipos = DB::table('equipos')
            ->where('id_tipo_equipo', $id_tipo_equipo)
            ->where('is_deleted', false)
            ->exists();

        if ($tieneEquipos) {
            return false;
        }

        return true;
    }
}