<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Orden;

class OrdenService
{
    public static function getAll()
    {

        //join con tabla clientes para obtener el nombre y cedula del cliente
        //join con membresias y planes para obtener el icono si tiene membresia activa
        $ordenes = DB::table('ordenes')
            ->join('clientes', 'ordenes.id_cliente', '=', 'clientes.id_cliente')
            ->leftJoin('membresias', function ($join) {
            $join->on('clientes.id_cliente', '=', 'membresias.id_cliente')
                ->where('membresias.estado', '=', 'Activa');
        })
            ->leftJoin('planes_membresias', 'membresias.id_plan_membresia', '=', 'planes_membresias.id_plan_membresia')
            ->select('ordenes.*', 'clientes.nombre', 'clientes.cedula', 'planes_membresias.imagePath as plan_image_path', 'planes_membresias.nombre as active_plan_nombre')
            ->addSelect('ordenes.porcentaje_avance_global as porcentaje_avance')
            ->orderByDesc('ordenes.estado_last_update')
            ->orderByDesc('ordenes.id_orden')
            ->get();
        return $ordenes;
    }

    public static function getOne($id)
    {
        $orden = Orden::find($id);
        return $orden;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $data['findes_laborables'] = true;
        $orden = Orden::create($data);

        DB::commit();
        return $orden;
    }

    public static function update($id, $data)
    {

        $orden = Orden::find($id);

        if (!$orden) {
            return null;
        }

        DB::beginTransaction();
        $orden->update($data);
        DB::commit();
        return $orden;
    }

    public static function delete($id)
    {
        $orden = Orden::find($id);

        if (!$orden) {
            return null;
        }

        DB::beginTransaction();
        $orden->delete();
        DB::commit();
        return $orden;
    }

    public static function getOrdenesByCliente($id_cliente)
    {

        $ordenes = Orden::where('id_cliente', $id_cliente)
            ->select('*')
            ->addSelect('ordenes.porcentaje_avance_global as porcentaje_avance')
            ->orderByDesc('ordenes.estado_last_update')
            ->orderByDesc('ordenes.id_orden')
            ->get();
        return $ordenes;
    }

    public static function getOrdenesByOperativo($id_operativo)
    {

        // Subconsulta para calcular el ingreso acumulado del operativo en la orden
        $ingresoSub = DB::table('ordenes_servicios')
            ->join('ordenes_servicios_operativos', 'ordenes_servicios.id_orden_servicio', '=', 'ordenes_servicios_operativos.id_orden_servicio')
            ->join('ordenes_servicios_especialidades', function ($join) {
            $join->on('ordenes_servicios_operativos.id_orden_servicio', '=', 'ordenes_servicios_especialidades.id_orden_servicio')
                ->on('ordenes_servicios_operativos.id_especialidad', '=', 'ordenes_servicios_especialidades.id_especialidad');
        })
            ->whereColumn('ordenes_servicios.id_orden', 'ordenes.id_orden')
            ->where('ordenes_servicios_operativos.id_operativo', $id_operativo)
            ->selectRaw('COALESCE(SUM(ordenes_servicios_especialidades.horas_hombre * ordenes_servicios_especialidades.tarifa_hora), 0)');

        $ordenes = DB::table('ordenes')
            ->join('clientes', 'ordenes.id_cliente', '=', 'clientes.id_cliente')
            ->leftJoin('membresias', function ($join) {
            $join->on('clientes.id_cliente', '=', 'membresias.id_cliente')
                ->where('membresias.estado', '=', 'Activa');
        })
            ->leftJoin('planes_membresias', 'membresias.id_plan_membresia', '=', 'planes_membresias.id_plan_membresia')
            ->join('ordenes_servicios', 'ordenes.id_orden', '=', 'ordenes_servicios.id_orden')
            ->join('ordenes_servicios_operativos', 'ordenes_servicios.id_orden_servicio', '=', 'ordenes_servicios_operativos.id_orden_servicio')
            ->where('ordenes_servicios_operativos.id_operativo', $id_operativo)
            ->whereIn('ordenes.estado', ['En espera', 'En ejecucion', 'Completada'])
            ->select('ordenes.*', 'clientes.nombre', 'clientes.cedula', 'planes_membresias.imagePath as plan_image_path', 'planes_membresias.nombre as active_plan_nombre')
            ->addSelect('ordenes.porcentaje_avance_global as porcentaje_avance')
            ->selectSub($ingresoSub, 'ingreso_operativo')
            ->orderByDesc('ordenes.estado_last_update')
            ->orderByDesc('ordenes.id_orden')
            ->distinct()
            ->get();
        return $ordenes;
    }

    /**
     * Reprograma una orden y todos sus sub-registros basándose en la fecha actual.
     */
    public static function reprogramarSegunEjecucion($id_orden)
    {
        $orden = Orden::find($id_orden);
        if (!$orden || !$orden->fecha_inicio)
            return null;

        $now = now();
        $fechaInicioOriginal = \Carbon\Carbon::parse($orden->fecha_inicio);
        $isWorkingDaysOnly = !$orden->findes_laborables;

        $today = $now->copy()->startOfDay();
        $originalDay = $fechaInicioOriginal->copy()->startOfDay();

        $daysToAdd = $isWorkingDaysOnly ? $originalDay->diffInWeekdays($today, false) : $originalDay->diffInDays($today, false);

        $nuevaFechaInicio = $fechaInicioOriginal->copy();
        if ($isWorkingDaysOnly) {
            $nuevaFechaInicio->addWeekdays($daysToAdd);
        } else {
            $nuevaFechaInicio->addDays($daysToAdd);
        }

        // 2. Ajuste Fino: Si la nueva fecha de inicio (hoy + hora original) es anterior a "ahora mismo", sumar 1 día más (hábil o calendario).
        if ($nuevaFechaInicio->lt($now)) {
            $daysToAdd += 1;
        }

        if ($daysToAdd == 0)
            return $orden; // No hay desfase

        $addFn = function ($fecha) use ($daysToAdd, $isWorkingDaysOnly) {
            if (!$fecha) return null;
            $d = \Carbon\Carbon::parse($fecha);
            return $isWorkingDaysOnly ? $d->addWeekdays($daysToAdd) : $d->addDays($daysToAdd);
        };

        DB::beginTransaction();
        try {
            // Actualizar Orden
            $orden->fecha_inicio = $addFn($orden->fecha_inicio);
            $orden->fecha_fin = $addFn($orden->fecha_fin);
            $orden->save();

            // Actualizar Servicios
            DB::table('ordenes_servicios')->where('id_orden', $id_orden)->get()->each(function ($os) use ($addFn) {
                DB::table('ordenes_servicios')->where('id_orden_servicio', $os->id_orden_servicio)->update([
                    'fecha_inicio' => $addFn($os->fecha_inicio),
                    'fecha_fin' => $addFn($os->fecha_fin)
                ]);
            });

            // Actualizar Operativos asignados
            DB::table('ordenes_servicios_operativos')
                ->join('ordenes_servicios', 'ordenes_servicios_operativos.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
                ->where('ordenes_servicios.id_orden', $id_orden)
                ->select('ordenes_servicios_operativos.id_orden_servicio_operativo', 'ordenes_servicios_operativos.fecha_inicio', 'ordenes_servicios_operativos.fecha_fin')
                ->get()
                ->each(function ($oso) use ($addFn) {
                DB::table('ordenes_servicios_operativos')
                    ->where('id_orden_servicio_operativo', $oso->id_orden_servicio_operativo)
                    ->update([
                    'fecha_inicio' => $addFn($oso->fecha_inicio),
                    'fecha_fin' => $addFn($oso->fecha_fin)
                ]);
            });

            // Actualizar Equipos asignados
            DB::table('ordenes_servicios_equipos')
                ->join('ordenes_servicios', 'ordenes_servicios_equipos.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
                ->where('ordenes_servicios.id_orden', $id_orden)
                ->select('ordenes_servicios_equipos.id_orden_servicio_equipo', 'ordenes_servicios_equipos.fecha_inicio', 'ordenes_servicios_equipos.fecha_fin')
                ->get()
                ->each(function ($ose) use ($addFn) {
                DB::table('ordenes_servicios_equipos')
                    ->where('id_orden_servicio_equipo', $ose->id_orden_servicio_equipo)
                    ->update([
                    'fecha_inicio' => $addFn($ose->fecha_inicio),
                    'fecha_fin' => $addFn($ose->fecha_fin)
                ]);
            });

            DB::commit();
            return $orden;
        }
        catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Busca solapamientos de agenda para el personal y equipos asignados a esta orden.
     */
    public static function detectarConflictos($id_orden)
    {
        $conflictos = [
            'operativos' => [],
            'equipos' => []
        ];

        // 1. Obtener todas las asignaciones de la orden
        $serviciosIds = DB::table('ordenes_servicios')->where('id_orden', $id_orden)->pluck('id_orden_servicio');

        $opAsignados = DB::table('ordenes_servicios_operativos')
            ->whereIn('id_orden_servicio', $serviciosIds)
            ->get();

        $eqAsignados = DB::table('ordenes_servicios_equipos')
            ->whereIn('id_orden_servicio', $serviciosIds)
            ->get();

        // 2. Verificar traslapes para Operativos
        foreach ($opAsignados as $asignacion) {
            $overlaps = DB::table('ordenes_servicios_operativos')
                ->join('ordenes_servicios', 'ordenes_servicios_operativos.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
                ->join('ordenes', 'ordenes_servicios.id_orden', '=', 'ordenes.id_orden')
                ->join('operativos', 'ordenes_servicios_operativos.id_operativo', '=', 'operativos.id_operativo')
                ->where('ordenes_servicios_operativos.id_operativo', $asignacion->id_operativo)
                ->where('ordenes_servicios_operativos.id_orden_servicio_operativo', '!=', $asignacion->id_orden_servicio_operativo)
                ->whereIn('ordenes.estado', ['En espera', 'En ejecucion'])
                ->where(function ($query) use ($asignacion) {
                $query->where('ordenes_servicios_operativos.fecha_inicio', '<', $asignacion->fecha_fin)
                    ->where('ordenes_servicios_operativos.fecha_fin', '>', $asignacion->fecha_inicio);
            })
                ->select('ordenes.id_orden', 'operativos.nombre as operativo_nombre', 'ordenes_servicios_operativos.fecha_inicio', 'ordenes_servicios_operativos.fecha_fin')
                ->get();

            foreach ($overlaps as $overlap) {
                $conflictos['operativos'][] = [
                    'nombre' => $overlap->operativo_nombre,
                    'id_orden_conflicto' => $overlap->id_orden,
                    'desde' => $overlap->fecha_inicio,
                    'hasta' => $overlap->fecha_fin
                ];
            }
        }

        // 3. Verificar traslapes para Equipos
        foreach ($eqAsignados as $asignacion) {
            $overlaps = DB::table('ordenes_servicios_equipos')
                ->join('ordenes_servicios', 'ordenes_servicios_equipos.id_orden_servicio', '=', 'ordenes_servicios.id_orden_servicio')
                ->join('ordenes', 'ordenes_servicios.id_orden', '=', 'ordenes.id_orden')
                ->join('equipos', 'ordenes_servicios_equipos.id_equipo', '=', 'equipos.id_equipo')
                ->join('tipos_equipos', 'equipos.id_tipo_equipo', '=', 'tipos_equipos.id_tipo_equipo')
                ->where('ordenes_servicios_equipos.id_equipo', $asignacion->id_equipo)
                ->where('ordenes_servicios_equipos.id_orden_servicio_equipo', '!=', $asignacion->id_orden_servicio_equipo)
                ->whereIn('ordenes.estado', ['En espera', 'En ejecucion'])
                ->where(function ($query) use ($asignacion) {
                $query->where('ordenes_servicios_equipos.fecha_inicio', '<', $asignacion->fecha_fin)
                    ->where('ordenes_servicios_equipos.fecha_fin', '>', $asignacion->fecha_inicio);
            })
                ->select('ordenes.id_orden', 'tipos_equipos.nombre as equipo_nombre', 'equipos.modelo', 'ordenes_servicios_equipos.fecha_inicio', 'ordenes_servicios_equipos.fecha_fin')
                ->get();

            foreach ($overlaps as $overlap) {
                $conflictos['equipos'][] = [
                    'nombre' => $overlap->equipo_nombre . ' (' . $overlap->modelo . ')',
                    'id_orden_conflicto' => $overlap->id_orden,
                    'desde' => $overlap->fecha_inicio,
                    'hasta' => $overlap->fecha_fin
                ];
            }
        }

        return $conflictos;
    }
}