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
            'direccion' => 'required|string|max:100',
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

        //validar que el cliente esta activo
        // $cliente = Cliente::where('correo', $user->email)->first();
        // if ($cliente->activo == 0) {
        //     return response([
        //         'message' => 'El cliente no esta activo',
        //     ], 401);
        // }

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
}