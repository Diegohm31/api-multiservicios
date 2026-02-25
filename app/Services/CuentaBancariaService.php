<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\CuentaBancaria;

class CuentaBancariaService
{
    public static function getAll()
    {
        $cuentas_bancarias = CuentaBancaria::get();
        return $cuentas_bancarias;
    }

    public static function getOne($id)
    {
        $cuenta_bancaria = CuentaBancaria::find($id);
        return $cuenta_bancaria;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $cuenta_bancaria = CuentaBancaria::create($data);

        DB::commit();
        return $cuenta_bancaria;
    }

    public static function update($id, $data)
    {

        $cuenta_bancaria = CuentaBancaria::find($id);

        if (!$cuenta_bancaria) {
            return null;
        }

        DB::beginTransaction();
        $cuenta_bancaria->update($data);
        DB::commit();
        return $cuenta_bancaria;
    }

    public static function delete($id)
    {
        $cuenta_bancaria = CuentaBancaria::find($id);

        if (!$cuenta_bancaria) {
            return null;
        }

        DB::beginTransaction();
        $cuenta_bancaria->delete();
        DB::commit();
        return $cuenta_bancaria;
    }

}