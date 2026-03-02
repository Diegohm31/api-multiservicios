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
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\ReportePagoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\AvanceOrdenController;
use App\Http\Controllers\PlanMembresiaController;
use App\Http\Controllers\PlanMembresiaTipoServicioController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\CuentaBancariaController;

// rutas para la autenticacion
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/send-confirmation-email', [AuthController::class, 'sendConfirmationEmail']);

Route::get('/tipos-servicios', [TipoServicioController::class, 'index']);
Route::get('/empresas', [EmpresaController::class, 'index']);
Route::get('/planes-membresias', [PlanMembresiaController::class, 'index']);
Route::post('/enviar-duda', [AuthController::class, 'enviarDudaHelpCenter']);

// proteger con sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Route::put('/clientes/cambiar-estado/{id_cliente}', [ClienteController::class, 'cambiarEstado']);

    Route::put('/ordenes/{id}/cancelar', [OrdenController::class, 'cancelarOrden']);
    Route::put('/ordenes/{id}/aceptar', [OrdenController::class, 'aceptarOrden']);
    Route::put('/ordenes/{id}/completar', [OrdenController::class, 'completarOrden']);

    Route::put('/presupuestos/{id}/aceptar', [PresupuestoController::class, 'aceptarPresupuesto']);
    Route::put('/presupuestos/{id}/cancelar', [PresupuestoController::class, 'cancelarPresupuesto']);
    Route::post('/ordenes/{id}/subir-peritaje', [OrdenController::class, 'subirPeritaje']);
    Route::put('/ordenes/{id}/asignar-personal', [OrdenController::class, 'asignarPersonal']);
    Route::get('/ordenes/{id}/asignar-personal', [OrdenController::class, 'getOneOrdenAsignarPersonal']);
    Route::get('/get-all-asignaciones', [OrdenController::class, 'getAllAsignaciones']);
    Route::put('/reportes-pagos/aceptar', [ReportePagoController::class, 'aceptarReportePago']);
    Route::put('/reportes-pagos/cancelar', [ReportePagoController::class, 'cancelarReportePago']);
    Route::put('/ordenes/{id}/poner-en-ejecucion', [OrdenController::class, 'ponerEnEjecucion']);
    Route::get('/ordenes/{id}/avances', [OrdenController::class, 'getOneOrdenAvances']);
    Route::get('/operativos/with-deleted', [OperativoController::class, 'indexWithDeleted']);

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
        'presupuestos' => PresupuestoController::class,
        'reportes-pagos' => ReportePagoController::class,
        'notificaciones' => NotificacionController::class,
        'avances-ordenes' => AvanceOrdenController::class,
        'membresias' => MembresiaController::class,
        'planes-membresias-tipos-servicios' => PlanMembresiaTipoServicioController::class,
        'cuentas-bancarias' => CuentaBancariaController::class,
    ]);

    Route::get('/planes-membresias/{id}', [PlanMembresiaController::class, 'show']);
    Route::post('/planes-membresias', [PlanMembresiaController::class, 'store']);
    Route::put('/planes-membresias/{id}', [PlanMembresiaController::class, 'update']);
    Route::delete('/planes-membresias/{id}', [PlanMembresiaController::class, 'destroy']);

    Route::get('/membresias/{id}', [MembresiaController::class, 'show']);
    Route::post('/membresias', [MembresiaController::class, 'store']);
    Route::put('/membresias/{id}', [MembresiaController::class, 'update']);
    Route::delete('/membresias/{id}', [MembresiaController::class, 'destroy']);

    Route::get('/tipos-servicios/{id}', [TipoServicioController::class, 'show']);
    Route::post('/tipos-servicios', [TipoServicioController::class, 'store']);
    Route::put('/tipos-servicios/{id}', [TipoServicioController::class, 'update']);
    Route::delete('/tipos-servicios/{id}', [TipoServicioController::class, 'destroy']);
    Route::get('/catalogo-servicios', [ServicioController::class, 'catalogoServicios']);

    Route::put('/usuarios/{id}/cambiar-estado', [AuthController::class, 'cambiarEstado']);
    Route::get('/menu', [AuthController::class, 'getMenu']);
    Route::get('/menu/{id_padre}', [AuthController::class, 'getMenuByPadre']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/user/verify-email', [AuthController::class, 'verifyEmailChange']);
    Route::post('/user/password', [AuthController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Es totalmente equivalente a lo que se hace con la funcion apiResources
// Route::get('/clientes', [ClienteController::class, 'index']);
// Route::get('/clientes/{cliente}', [ClienteController::class, 'show']);
// Route::post('/clientes', [ClienteController::class, 'store']);
// Route::put('/clientes/{cliente}', [ClienteController::class, 'update']);
// Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy']);