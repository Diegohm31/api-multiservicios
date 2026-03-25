<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
//use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\MailerService;
use App\Services\ClienteService;
use App\Services\UserService;
use App\Services\OpcionService;
use App\Services\NotificacionService;
use App\Services\OperativoService;
use App\Services\AdminService;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|max:255',
            'cedula' => 'required|string|max:11|unique:clientes,cedula',
            'telefono' => 'string|max:11|unique:clientes,telefono',
            'direccion' => 'required|string|max:1000',
        ]);

        $data = $request->all();
        //asignar rol de cliente
        $data['id_rol'] = "00001";

        $user = UserService::store($data);

        // $user = User::create([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'password' => Hash::make($request->password),
        //     'id_rol' => 1,
        //     'activo' => true,
        // ]);

        MailerService::enviarCorreo([
            'to' => [$user->email],
            'cc' => [],
            'bcc' => [],
        ], 'Bienvenido', 'emails.register', ['nombre' => $user->name, 'email' => $user->email, 'password' => $request->password]);

        //grabar registro en la tabla notificaciones
        $notificacion = NotificacionService::store([
            'id_user' => $user->id,
            'asunto' => 'Bienvenido',
            'fecha_envio' => date('Y-m-d H:i:s'),
        ]);

        $data['id_user'] = $user->id;

        $cliente = ClienteService::create($data);

        return response([
            'message' => 'Usuario registrado exitosamente',
            'data' => $cliente,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = UserService::getOne($request->email);

        // $user = User::where('email', $request->email)->first();

        // if (!$user || !Hash::check($request->password, $user->password)) {
        //     return response([
        //         'message' => 'Las credenciales proporcionadas son incorrectas.',
        //     ], 401);
        // }

        if (!$user) {
            return response([
                'message' => 'El usuario no existe',
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'La contraseña es incorrecta',
            ], 401);
        }

        // Validar que el usuario esté activo (especialmente para clientes y operativos)
        if (in_array($user->id_rol, ['00001', '00002']) && !$user->activo) {
            return response([
                'message' => 'Su cuenta se encuentra inactiva.',
            ], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response([
            'message' => 'Usuario autenticado exitosamente',
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        //dd($request->user());
        // $request->user()->currentAccessToken()->delete();
        $request->user()->tokens()->delete();

        return response([
            'message' => 'Sesión cerrada y token eliminado',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = UserService::getOne($request->email);

        if (!$user) {
            return response([
                'message' => 'El usuario no existe',
            ], 401);
        }

        $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        //url que redirige al frontend
        // $url = 'http://multiservicios.local/new_password/' . $user->email;

        // obtener dominio host y protocolo http o https que hizo el request
        $origin = $request->header('Origin');

        $url = $origin . '/new_password/' . $user->email;

        MailerService::enviarCorreo([
            'to' => [$user->email],
            'cc' => [],
            'bcc' => [],
        ], 'Codigo de verificacion', 'emails.password_code', ['nombre' => $user->name, 'codigo' => $codigo, 'url' => $url]);

        //grabar registro en la tabla notificaciones
        $notificacion = NotificacionService::store([
            'id_user' => $user->id,
            'asunto' => 'Codigo de verificacion',
            'fecha_envio' => date('Y-m-d H:i:s'),
        ]);

        //guardar el codigo en la base de datos
        $user->codigo_verificacion = $codigo;
        $user->save();

        return response([
            'message' => 'Codigo de verificacion enviado exitosamente',
        ]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'codigo' => 'required|string',
        ]);

        $user = UserService::getOne($request->email);

        if (!$user || $user->codigo_verificacion !== $request->codigo) {
            return response([
                'message' => 'El código de verificación es incorrecto',
            ], 401);
        }

        return response([
            'message' => 'Código validado correctamente',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = UserService::getOne($request->email);

        if (!$user) {
            return response([
                'message' => 'El usuario no existe',
            ], 401);
        }

        $user->password = Hash::make($request->password);
        $user->codigo_verificacion = null;
        $user->save();

        MailerService::enviarCorreo([
            'to' => [$user->email],
            'cc' => [],
            'bcc' => [],
        ], 'Contraseña actualizada', 'emails.password_changed_notification', ['nombre' => $user->name, 'email' => $user->email, 'password' => $request->password]);

        //grabar registro en la tabla notificaciones
        $notificacion = NotificacionService::store([
            'id_user' => $user->id,
            'asunto' => 'Contraseña actualizada',
            'fecha_envio' => date('Y-m-d H:i:s'),
        ]);

        return response([
            'message' => 'Contraseña actualizada exitosamente',
        ]);
    }
    public function getMenu(Request $request)
    {
        // se obtiene el user que realizo la peticion API
        $user = $request->user();
        $menu = OpcionService::getMenu($user->id_rol);

        return response([
            'message' => $menu ? 'Menu obtenido exitosamente' : 'No se encontro menu',
            'data' => $menu,
        ]);
    }

    public function getUser(Request $request)
    {
        $userRequest = $request->user();

        $user = UserService::getOneById($userRequest->id);

        return response([
            'message' => 'Usuario obtenido exitosamente',
            'data' => $user,
        ]);
    }

    public function getMenuByPadre(Request $request, $id_padre)
    {
        // se obtiene el user que realizo la peticion API
        $user = $request->user();
        $menu = OpcionService::getMenuByPadre($user->id_rol, $id_padre);

        return response([
            'message' => $menu ? 'Menu obtenido exitosamente' : 'No se encontro menu',
            'data' => $menu,
        ]);
    }

    public function cambiarEstado(Request $request, $id)
    {

        $request->validate([
            'active' => 'required|boolean',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response([
                'message' => 'El usuario no existe',
            ], 404);
        }

        $data = $request->all();

        if ($user->id_rol === '00002' && !$data['active']) {
            $operativo = OperativoService::getOneByUser($user->id);
            if ($operativo && OperativoService::tieneAsignacionesActivas($operativo->id_operativo)) {
                return response([
                    'message' => 'No se puede inactivar al operativo porque tiene asignaciones en ejecución o próximas a ejecutarse.',
                ], 400);
            }
        }

        $user->activo = $data['active'];
        $user->save();

        //determinar si el usuario es un cliente o un operativo
        switch ($user->id_rol) {
            case '00001':
                $cliente = ClienteService::getOneByUser($user->id);
                if ($cliente) {
                    $cliente->is_deleted = !$data['active'];
                    $cliente->save();
                }
                break;
            case '00002':
                $operativo = OperativoService::getOneByUser($user->id);
                if ($operativo) {
                    $operativo->is_deleted = !$data['active'];
                    $operativo->disponible = $data['active'];
                    $operativo->save();
                }
                break;
        }

        return response([
            'message' => 'Estado del usuario actualizado exitosamente',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'cedula' => 'nullable|string|max:11',
            'telefono' => 'nullable|string|max:11',
            'direccion' => 'nullable|string|max:100',
        ]);

        $data = $request->all();

        // Actualizar datos básicos del usuario (Solo el nombre, el correo se verifica después)
        $user->name = $data['name'];
        $user->save();

        // Actualizar datos específicos por rol (Sin permitir actualizar cédula)
        switch ($user->id_rol) {
            case '00001':
                $cliente = ClienteService::getOneByUser($user->id);
                if ($cliente) {
                    $cliente->nombre = $data['name'];
                    $cliente->telefono = $data['telefono'] ?? $cliente->telefono;
                    $cliente->direccion = $data['direccion'] ?? $cliente->direccion;
                    $cliente->save();
                }
                break;
            case '00002':
                $operativo = OperativoService::getOneByUser($user->id);
                if ($operativo) {
                    $operativo->nombre = $data['name'];
                    $operativo->telefono = $data['telefono'] ?? $operativo->telefono;
                    $operativo->save();
                }
                break;
            case '00003':
                $admin = AdminService::getOneByUser($user->id);
                if ($admin) {
                    $admin->nombre = $data['name'];
                    $admin->telefono = $data['telefono'] ?? $admin->telefono;
                    $admin->save();
                }
                break;
        }

        // Manejo de cambio de correo electrónico
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->codigo_verificacion = $codigo;
            $user->save();

            MailerService::enviarCorreo([
                'to' => [$data['email']],
            ], 'Verificación de cambio de correo', 'emails.email_change_code', [
                'nombre' => $user->name,
                'codigo' => $codigo,
                'nuevo_correo' => $data['email']
            ]);

            //grabar registro en la tabla notificaciones
            $notificacion = NotificacionService::store([
                'id_user' => $user->id,
                'asunto' => 'Cambio de correo electrónico',
                'fecha_envio' => date('Y-m-d H:i:s'),
            ]);

            return response([
                'message' => 'Se ha enviado un código de verificación a su nuevo correo electrónico.',
                'email_change_pending' => true,
                'new_email' => $data['email'],
                'data' => UserService::getOneById($user->id)
            ]);
        }

        return response([
            'message' => 'Perfil actualizado exitosamente',
            'data' => UserService::getOneById($user->id)
        ]);
    }

    public function verifyEmailChange(Request $request)
    {
        $request->validate([
            'new_email' => 'required|email|unique:users,email',
            'codigo' => 'required|string',
        ]);

        $user = $request->user();

        if ($user->codigo_verificacion !== $request->codigo) {
            return response([
                'message' => 'El código de verificación es incorrecto',
            ], 422);
        }

        // Actualizar el correo
        $user->email = $request->new_email;
        $user->codigo_verificacion = null;
        $user->save();

        // Cerrar sesión por seguridad
        $user->tokens()->delete();

        return response([
            'message' => 'Correo electrónico actualizado correctamente. Por seguridad, su sesión se ha cerrado.',
            'logout' => true
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response([
                'message' => 'La contraseña actual es incorrecta',
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response([
            'message' => 'Contraseña actualizada exitosamente',
        ]);
    }

    public function enviarDudaHelpCenter(Request $request)
    {
        $request->validate([
            'duda' => 'required|string',
            'email' => 'required|email',
        ]);

        // Obtener todos los administradores (id_rol = '00003')
        $admins = User::where('id_rol', '00003')->get();

        if ($admins->isEmpty()) {
            return response([
                'message' => 'No se encontraron administradores para recibir la consulta.',
            ], 404);
        }

        foreach ($admins as $admin) {
            MailerService::enviarCorreo([
                'to' => [$admin->email],
                'cc' => [],
                'bcc' => [],
            ], 'Nueva consulta del Centro de Ayuda', 'emails.ayuda_cliente', [
                'admin_nombre' => $admin->name,
                'cliente_email' => $request->email,
                'duda' => $request->duda
            ]);

            //grabar registro en la tabla notificaciones
            $notificacion = NotificacionService::store([
                'id_user' => $admin->id,
                'asunto' => 'Nueva consulta del Centro de Ayuda',
                'fecha_envio' => date('Y-m-d H:i:s'),
            ]);
        }

        return response([
            'message' => 'Consulta enviada exitosamente a los administradores.',
        ]);
    }
}