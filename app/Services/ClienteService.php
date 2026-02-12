<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Cliente;
// use App\Models\Promocion;


class ClienteService
{
    public static function getAll()
    {
        $clientes = Cliente::get();
        return $clientes;
    }

    public static function getOne($id)
    {
        $cliente = Cliente::find($id);
        return $cliente;
    }

    public static function create($data)
    {

        DB::beginTransaction();
        $data['nombre'] = $data['name'];
        $cliente = Cliente::create($data);

        DB::commit();
        return $cliente;
    }

    public static function update($id, $data)
    {

        $cliente = Cliente::find($id);

        if (!$cliente) {
            return null;
        }

        DB::beginTransaction();
        $cliente->update($data);
        DB::commit();
        return $cliente;
    }

    public static function delete($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return null;
        }

        DB::beginTransaction();
        $cliente->delete();
        DB::commit();
        return $cliente;
    }

    public static function getOneByUser($id_user)
    {
        $cliente = Cliente::where('id_user', $id_user)->first();
        return $cliente;
    }

    // public static function enviarPromocion()
    // {
    //     $clientes = Cliente::get()->where('activo', 1);
    //     $promociones = Promocion::get()->where('activa', 1);

    //     foreach ($clientes as $cliente) {
    //         // foreach ($promociones as $promocion) {
    //         //     MailerService::enviarCorreo([
    //         //         'to' => [$cliente->correo],
    //         //         'cc' => [],
    //         //         'bcc' => [],
    //         //     ], 'Promoción', 'emails.promocion', ['nombre' => $cliente->cliente, 'promocion' => $promocion->promocion, 'descripcion' => $promocion->descripcion, 'imagePath' => $promocion->imagePath]);
    //         // }
    //         // enviar todas las promociones a un solo cliente
    //         MailerService::enviarCorreo([
    //             'to' => [$cliente->correo],
    //             'cc' => [],
    //             'bcc' => [],
    //         ], 'Promoción', 'emails.promocion', ['nombre' => $cliente->cliente, 'promociones' => $promociones]);
    //     }

    //     return $clientes;
    // }
}