<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public static function store($data)
    {
        $data["password"] = Hash::make($data['password']);
        $data["activo"] = true;
        $user = User::create($data);
        return $user;
    }

    public static function getOne($email)
    {
        $user = User::where('email', $email)->first();
        return $user;
    }

    public static function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return null;
        }
        //borrado logico
        $user->update(['activo' => false]);
        return $user;
    }

    public static function getOneById($id)
    {
        $user = User::find($id);

        switch ($user->id_rol) {
            case '00001':
                $user = User::join('clientes', 'users.id', '=', 'clientes.id_user')->where('users.id', $id)->first();
                break;

            case '00002':
                $user = User::join('operativos', 'users.id', '=', 'operativos.id_user')->where('users.id', $id)->first();
                break;

            case '00003':
                $user = User::join('admins', 'users.id', '=', 'admins.id_user')->where('users.id', $id)->first();
                break;

        }
        return $user;
    }
    public static function update($id, $data)
    {
        $user = User::find($id);
        if (!$user) {
            return null;
        }
        $user->update($data);
        return $user;
    }
}