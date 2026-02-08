<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Material;
use App\Models\ServicioMaterial;
use App\Models\Servicio;

class MaterialService
{
    public static function getAll()
    {
        $materiales = Material::get();
        return $materiales;
    }

    public static function getOne($id)
    {
        $material = Material::find($id);
        return $material;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $data['stock_actual'] = 0;
        $material = Material::create($data);
        DB::commit();
        return $material;
    }

    public static function update($id, $data)
    {
        $material = Material::find($id);

        if (!$material) {
            return null;
        }

        $precio_actual = $material->precio_unitario;

        DB::beginTransaction();
        $material->update($data);

        // Solo ejecutar si realmente cambió el precio y viene en la data
        if (isset($data['precio_unitario']) && $data['precio_unitario'] != $precio_actual) {
            $diferencia_unitaria = $data['precio_unitario'] - $precio_actual;

            // Traemos los registros de la tabla pivote para tener la 'cantidad'
            $relaciones = ServicioMaterial::where('id_material', $id)->get();

            foreach ($relaciones as $relacion) {
                $servicio = Servicio::find($relacion->id_servicio);

                if ($servicio) {
                    // Calculamos el ajuste real: diferencia * cantidad usada
                    $ajuste_total = $diferencia_unitaria * $relacion->cantidad;

                    $servicio->precio_materiales += $ajuste_total;
                    $servicio->precio_general += $ajuste_total;

                    $servicio->save();
                }
            }
        }

        DB::commit();
        return $material;
    }

    public static function delete($id)
    {
        $material = Material::find($id);

        if (!$material) {
            return null;
        }

        DB::beginTransaction();
        $material->delete();
        DB::commit();
        return $material;
    }

}