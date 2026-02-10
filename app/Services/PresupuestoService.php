<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Presupuesto;

class PresupuestoService
{
    public static function getAll()
    {
        $presupuestos = Presupuesto::get();
        return $presupuestos;
    }

    public static function getOne($id)
    {
        $presupuesto = Presupuesto::find($id);
        return $presupuesto;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $data['estado'] = 'Pendiente';
        $data['fecha_emision'] = date('Y-m-d');
        $presupuesto = Presupuesto::create($data);

        DB::commit();
        return $presupuesto;
    }

    public static function update($id, $data)
    {

        $presupuesto = Presupuesto::find($id);

        if (!$presupuesto) {
            return null;
        }

        DB::beginTransaction();
        $presupuesto->update($data);
        DB::commit();
        return $presupuesto;
    }

    public static function delete($id)
    {
        $presupuesto = Presupuesto::find($id);

        if (!$presupuesto) {
            return null;
        }

        DB::beginTransaction();
        $presupuesto->delete();
        DB::commit();
        return $presupuesto;
    }

}