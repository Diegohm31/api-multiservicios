<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Empresa;

class EmpresaService
{
    public static function getAll()
    {
        $empresas = Empresa::get();
        return $empresas;
    }

    public static function getOne($id)
    {
        $empresa = Empresa::find($id);
        return $empresa;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $empresa = Empresa::create($data);

        DB::commit();
        return $empresa;
    }

    public static function update($id, $data)
    {

        $empresa = Empresa::find($id);

        if (!$empresa) {
            return null;
        }

        DB::beginTransaction();
        $empresa->update($data);
        DB::commit();
        return $empresa;
    }

    public static function delete($id)
    {
        $empresa = Empresa::find($id);

        if (!$empresa) {
            return null;
        }

        DB::beginTransaction();
        $empresa->delete();
        DB::commit();
        return $empresa;
    }

}