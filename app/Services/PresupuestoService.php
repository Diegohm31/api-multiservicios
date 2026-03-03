<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Presupuesto;
use App\Models\Orden;
use App\Models\User;
use App\Services\ClienteService;
use App\Services\MailerService;
use App\Services\NotificacionService;
use Illuminate\Support\Carbon;

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
        $data['fecha_emision'] = Carbon::now();
        $data['fecha_vencimiento'] = Carbon::parse($data['fecha_emision'])->addDays(5);
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

    public static function checkPresupuestos()
    {
        $presupuestos = Presupuesto::where('estado', 'Pendiente')->where('fecha_vencimiento', '<=', Carbon::now())->get();

        if ($presupuestos->isEmpty()) {
            return;
        }

        foreach ($presupuestos as $presupuesto) {
            /** @var Presupuesto $presupuesto */
            $presupuesto->estado = 'Cancelado';
            $presupuesto->save();

            $orden = Orden::where('id_presupuesto', $presupuesto->id_presupuesto)->first();
            $orden->estado = 'Aceptada';
            $orden->save();

            //buscar al cliente
            $cliente = ClienteService::getOne($orden->id_cliente);
            //buscar al usuario
            $user = User::find($cliente->id_user);

            MailerService::enviarCorreo([
                'to' => [$user->email],
                'cc' => [],
                'bcc' => [],
            ], 'Presupuesto Vencido', 'emails.presupuesto_vencido', ['nombre' => $user->name, 'id_orden' => $orden->id_orden]);

            //grabar registro en la tabla notificaciones
            $notificacion = NotificacionService::store([
                'id_user' => $user->id,
                'asunto' => 'Presupuesto Vencido',
                'fecha_envio' => date('Y-m-d H:i:s'),
            ]);
        }
    }

}