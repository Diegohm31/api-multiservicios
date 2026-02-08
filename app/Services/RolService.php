<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Rol;

class RolService
{
    public static function getAll()
    {
        $roles = Rol::get();
        return $roles;
    }

    public static function getOne($id)
    {
        $rol = Rol::find($id);
        return $rol;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $rol = Rol::create($data);

        DB::commit();
        return $rol;
    }

    public static function update($id, $data)
    {

        $rol = Rol::find($id);

        if (!$rol) {
            return null;
        }

        DB::beginTransaction();
        $rol->update($data);
        DB::commit();
        return $rol;
    }

    public static function delete($id)
    {
        $rol = Rol::find($id);

        if (!$rol) {
            return null;
        }

        DB::beginTransaction();
        $rol->delete();
        DB::commit();
        return $rol;
    }

}