<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\OpcionController;
use App\Http\Controllers\RolOpcionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\OperativoController;
use App\Http\Controllers\OperativoEspecialidadController;
use App\Http\Controllers\TipoEquipoController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MovimientoMaterialController;
use App\Http\Controllers\TipoServicioController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ServicioTipoEquipoController;
use App\Http\Controllers\ServicioMaterialController;
use App\Http\Controllers\ServicioEspecialidadController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\OrdenServicioController;

// rutas para la autenticacion
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/send-confirmation-email', [AuthController::class, 'sendConfirmationEmail']);

Route::get('/tipos-servicios', [TipoServicioController::class, 'index']);

// proteger con sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Route::put('/clientes/cambiar-estado/{id_cliente}', [ClienteController::class, 'cambiarEstado']);

    Route::apiResources([
        'clientes' => ClienteController::class,
        'roles' => RolController::class,
        'opciones' => OpcionController::class,
        'roles-opciones' => RolOpcionController::class,
        'admins' => AdminController::class,
        'especialidades' => EspecialidadController::class,
        'operativos' => OperativoController::class,
        'operativos-especialidades' => OperativoEspecialidadController::class,
        'tipos-equipos' => TipoEquipoController::class,
        'equipos' => EquipoController::class,
        'materiales' => MaterialController::class,
        'movimientos-materiales' => MovimientoMaterialController::class,
        'servicios' => ServicioController::class,
        'servicios-tipos-equipos' => ServicioTipoEquipoController::class,
        'servicios-materiales' => ServicioMaterialController::class,
        'servicios-especialidades' => ServicioEspecialidadController::class,
        'ordenes' => OrdenController::class,
        'ordenes-servicios' => OrdenServicioController::class,
    ]);

    Route::get('/tipos-servicios/{id}', [TipoServicioController::class, 'show']);
    Route::post('/tipos-servicios', [TipoServicioController::class, 'store']);
    Route::put('/tipos-servicios/{id}', [TipoServicioController::class, 'update']);
    Route::delete('/tipos-servicios/{id}', [TipoServicioController::class, 'destroy']);
    Route::get('/catalogo-servicios', [ServicioController::class, 'catalogoServicios']);

    Route::get('/menu', [AuthController::class, 'getMenu']);
    Route::get('/menu/{id_padre}', [AuthController::class, 'getMenuByPadre']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Es totalmente equivalente a lo que se hace con la funcion apiResources
// Route::get('/clientes', [ClienteController::class, 'index']);
// Route::get('/clientes/{cliente}', [ClienteController::class, 'show']);
// Route::post('/clientes', [ClienteController::class, 'store']);
// Route::put('/clientes/{cliente}', [ClienteController::class, 'update']);
// Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy']);