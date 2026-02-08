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

        DB::beginTransaction();
        //borrado logico
        $especialidad->update(['is_deleted' => true]);
        DB::commit();
        return $especialidad;
    }

}