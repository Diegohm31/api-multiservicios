<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\MovimientoMaterial;
use App\Models\Material;
use App\Models\User;
use App\Services\MailerService;
use App\Services\NotificacionService;

class MovimientoMaterialService
{
    public static function getAll()
    {
        $movimientos = MovimientoMaterial::orderBy('id_movimiento_material', 'asc')->get();
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
            // Permitir que precio_unitario sea opcional si no viene en data
            if (isset($data['precio_unitario'])) {
                $material->precio_unitario = $data['precio_unitario'];
            }
        } else {
            $material->stock_actual -= $data['cantidad'];
        }
        $material->save();

        DB::commit();

        // Preparar objeto de alerta si el stock cayó por debajo del mínimo
        $alerta = null;
        if ($data['tipo_movimiento'] == 'salida' && $material->stock_actual < $material->stock_minimo) {
            $alerta = (object) [
                'id_material' => $material->id_material,
                'nombre' => $material->nombre,
                'stock_actual' => $material->stock_actual,
                'stock_minimo' => $material->stock_minimo,
            ];
        }

        return (object) [
            'movimiento' => $movimiento,
            'alerta' => $alerta
        ];
    }

    /**
     * Envía un correo consolidado a los administradores con la lista de materiales bajo stock.
     */
    public static function notificarVariosStockBajo(array $materialesBajoStock)
    {
        if (empty($materialesBajoStock)) {
            return;
        }

        $admins = User::where('id_rol', '00003')->get();
        $asunto = "Alerta de Inventario: Stock bajo";

        foreach ($admins as $adminUser) {
            // Enviar correo
            MailerService::enviarCorreo(
                ['to' => [$adminUser->email]],
                $asunto,
                'emails.stock_bajo',
                [
                    'admin_nombre' => $adminUser->name,
                    'materiales' => $materialesBajoStock
                ]
            );

            // Registrar notificación
            NotificacionService::store([
                'id_user' => $adminUser->id,
                'asunto' => $asunto,
                'fecha_envio' => now(),
            ]);
        }
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