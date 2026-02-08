<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\MovimientoMaterial;
use App\Models\Material;

class MovimientoMaterialService
{
    public static function getAll()
    {
        $movimientos = MovimientoMaterial::get();
        return $movimientos;
    }

    public static function getOne($id)
    {
        $movimiento = MovimientoMaterial::find($id);
        return $movimiento;
    }

    public static function store($data)
    {
        //validar que el material exista y que en caso de que el moviento sea una salida se tenga stock suficiente
        $material = Material::find($data['id_material']);
        if (!$material) {
            return null;
        }
        if ($data['tipo_movimiento'] == 'salida' && $material->stock_actual < $data['cantidad']) {
            return null;
        }


        DB::beginTransaction();
        $data['fecha_movimiento'] = now();
        $movimiento = MovimientoMaterial::create($data);

        //actualizar el stock del material
        if ($data['tipo_movimiento'] == 'entrada') {
            $material->stock_actual += $data['cantidad'];
            $material->precio_unitario = $data['precio_unitario'];
        } else {
            $material->stock_actual -= $data['cantidad'];
        }
        $material->save();

        DB::commit();
        return $movimiento;
    }

    public static function update($id, $data)
    {

        $movimiento = MovimientoMaterial::find($id);

        if (!$movimiento) {
            return null;
        }

        DB::beginTransaction();
        $movimiento->update($data);
        DB::commit();
        return $movimiento;
    }

    public static function delete($id)
    {
        $movimiento = MovimientoMaterial::find($id);

        if (!$movimiento) {
            return null;
        }

        DB::beginTransaction();
        $movimiento->delete();
        DB::commit();
        return $movimiento;
    }

}