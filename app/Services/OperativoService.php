<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Operativo;

class OperativoService
{
    public static function getAll()
    {
        $operativos = Operativo::where('is_deleted', false)->get();
        return $operativos;
    }

    public static function getOne($id)
    {
        $operativo = Operativo::where('is_deleted', false)->find($id);
        return $operativo;
    }

    public static function create($data)
    {

        DB::beginTransaction();
        $data['nombre'] = $data['name'];
        $data['disponible'] = true;
        $data['reputacion'] = 0;
        $data['is_deleted'] = false;

        $operativo = Operativo::create($data);

        DB::commit();
        return $operativo;
    }

    public static function update($id, $data)
    {

        $operativo = Operativo::where('is_deleted', false)->find($id);

        if (!$operativo) {
            return null;
        }

        DB::beginTransaction();
        $operativo->update($data);
        DB::commit();
        return $operativo;
    }

    public static function delete($id)
    {
        $operativo = Operativo::where('is_deleted', false)->find($id);

        if (!$operativo) {
            return null;
        }

        DB::beginTransaction();
        //borrado logico
        $operativo->update(['is_deleted' => true]);
        DB::commit();
        return $operativo;
    }
}