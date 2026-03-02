<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Membresia;
use App\Services\PlanMembresiaService;
use App\Services\PlanMembresiaTipoServicioService;
use App\Services\NotificacionService;
use App\Services\ClienteService;
use App\Models\User;
use App\Services\MailerService;
use Illuminate\Support\Carbon;

class MembresiaService
{
    public static function getAll()
    {
        $membresias = Membresia::get();
        return $membresias;
    }

    public static function getOne($id)
    {
        $membresia = Membresia::find($id);
        return $membresia;
    }

    public static function store($data)
    {
        DB::beginTransaction();
        $data['estado'] = 'Pendiente';
        $membresia = Membresia::create($data);

        DB::commit();
        return $membresia;
    }

    public static function update($id, $data)
    {

        $membresia = Membresia::find($id);

        if (!$membresia) {
            return null;
        }

        DB::beginTransaction();
        $membresia->update($data);
        DB::commit();
        return $membresia;
    }

    public static function delete($id)
    {
        $membresia = Membresia::find($id);

        if (!$membresia) {
            return null;
        }

        DB::beginTransaction();
        $membresia->estado = 'Inactiva';
        $membresia->save();
        DB::commit();
        return $membresia;
    }

    public static function getActiveByCliente($id_cliente)
    {
        $membresia = Membresia::where('id_cliente', $id_cliente)
            ->where('estado', 'Activa')
            ->first();

        if ($membresia) {
            $plan = PlanMembresiaService::getOne($membresia->id_plan_membresia);
            if ($plan) {
                $plan->array_tipos_servicios = PlanMembresiaTipoServicioService::getOneByPlanMembresia($plan->id_plan_membresia);
                $membresia->plan = $plan;
            }
        }
        return $membresia;
    }

    public static function checkMembresias()
    {
        $membresias = Membresia::where('estado', 'Activa')->get();
        /** @var Membresia $membresia */
        foreach ($membresias as $membresia) {
            if (Carbon::parse($membresia->fecha_fin)->isToday()) {
                $membresia->estado = 'Inactiva';
                $membresia->save();

                //buscar al cliente
                $cliente = ClienteService::getOne($membresia->id_cliente);
                //buscar al usuario
                $user = User::find($cliente->id_user);
                //buscar el plan
                $plan = PlanMembresiaService::getOne($membresia->id_plan_membresia);

                MailerService::enviarCorreo([
                    'to' => [$user->email],
                    'cc' => [],
                    'bcc' => [],
                ], 'Membresia Inactivada', 'emails.membresia_inactivada', ['nombre' => $user->name, 'nombre_plan' => $plan->nombre]);

                //grabar registro en la tabla notificaciones
                $notificacion = NotificacionService::store([
                    'id_user' => $user->id,
                    'asunto' => 'Membresia Inactivada',
                    'fecha_envio' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public static function warningMembresias()
    {
        //enviar correo de aviso de vencimiento de membresia 7 dias antes de vencer
        $membresias = Membresia::where('estado', 'Activa')->get();
        /** @var Membresia $membresia */
        foreach ($membresias as $membresia) {
            // Comparar solo la fecha, ignorando la hora
            if (Carbon::parse($membresia->fecha_fin)->isSameDay(Carbon::now()->addDays(7))) {
                //buscar al cliente
                $cliente = ClienteService::getOne($membresia->id_cliente);
                //buscar al usuario
                $user = User::find($cliente->id_user);
                //buscar el plan
                $plan = PlanMembresiaService::getOne($membresia->id_plan_membresia);

                MailerService::enviarCorreo([
                    'to' => [$user->email],
                    'cc' => [],
                    'bcc' => [],
                ], 'Membresia Por Vencer', 'emails.membresia_por_vencer', ['nombre' => $user->name, 'nombre_plan' => $plan->nombre]);

                //grabar registro en la tabla notificaciones
                $notificacion = NotificacionService::store([
                    'id_user' => $user->id,
                    'asunto' => 'Membresia Por Vencer',
                    'fecha_envio' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}