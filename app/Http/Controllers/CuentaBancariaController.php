<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CuentaBancariaService;

class CuentaBancariaController extends Controller
{
    public function index()
    {
        $cuentas_bancarias = CuentaBancariaService::getAll();
        return $this->successResponse(
            $cuentas_bancarias,
            $cuentas_bancarias->isEmpty() ? 'No se encontraron cuentas bancarias' : 'Cuentas bancarias obtenidas correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_empresa' => 'required|string|max:100',
            'banco' => 'required|string|max:1000',
            'tipo_cuenta' => 'required|string|max:100',
            'telefono' => 'required|string|max:100',
            'numero_cuenta' => 'required|string|max:100',
            'pago_movil' => 'required|boolean',
        ]);

        $data = $request->all();

        $cuenta_bancaria = CuentaBancariaService::store($data);
        if (!$cuenta_bancaria) {
            return $this->errorResponse('No se pudo crear la cuenta bancaria', 404);
        }
        return $this->successResponse($cuenta_bancaria, 'Cuenta bancaria creada correctamente');
    }

    public function show($id)
    {
        $cuenta_bancaria = CuentaBancariaService::getOne($id);
        if (!$cuenta_bancaria) {
            return $this->errorResponse('Cuenta bancaria no encontrada', 404);
        }
        return $this->successResponse($cuenta_bancaria, 'Cuenta bancaria obtenida correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_empresa' => 'nullable|string|max:100',
            'banco' => 'nullable|string|max:1000',
            'tipo_cuenta' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:100',
            'numero_cuenta' => 'nullable|string|max:100',
            'pago_movil' => 'nullable|boolean',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('id_empresa') && !$request->has('banco') && !$request->has('tipo_cuenta') && !$request->has('telefono') && !$request->has('numero_cuenta') && !$request->has('pago_movil')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $cuenta_bancaria = CuentaBancariaService::update($id, $data);
        if (!$cuenta_bancaria) {
            return $this->errorResponse('Cuenta bancaria no encontrada', 404);
        }

        return $this->successResponse($cuenta_bancaria, 'Cuenta bancaria actualizada correctamente');
    }

    public function destroy(string $id)
    {
        $cuenta_bancaria = CuentaBancariaService::delete($id);
        if (!$cuenta_bancaria) {
            return $this->errorResponse('Cuenta bancaria no encontrada', 404);
        }
        return $this->successResponse($cuenta_bancaria, 'Cuenta bancaria eliminada correctamente');
    }
}
