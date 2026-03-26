<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PlanMembresiaService;
use App\Services\PlanMembresiaTipoServicioService;
use App\Models\Membresia;
use Illuminate\Support\Facades\DB;

class PlanMembresiaController extends Controller
{
    public function index()
    {
        $planes_membresias = PlanMembresiaService::getAll();

        foreach ($planes_membresias as $plan) {
            $plan->array_tipos_servicios = PlanMembresiaTipoServicioService::getOneByPlanMembresia($plan->id_plan_membresia);
        }

        return $this->successResponse(
            $planes_membresias,
            $planes_membresias->isEmpty() ? 'No se encontraron planes de membresia' : 'Planes de membresia obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:1000',
            'descripcion' => 'required|string|max:1000',
            'duracion_meses' => 'required|numeric',
            'precio' => 'required|numeric',
            'array_tipos_servicios' => 'required|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        $data = $request->all();

        DB::beginTransaction();
        try {
            $plan_membresia = PlanMembresiaService::store($data);
            if (!$plan_membresia) {
                DB::rollBack();
                return $this->errorResponse('No se pudo crear el plan de membresia', 404);
            }

            // crear registros en la tabla planes_membresias_tipos_servicios
            foreach ($request->array_tipos_servicios as $tipo_servicio) {
                PlanMembresiaTipoServicioService::store([
                    'id_plan_membresia' => $plan_membresia->id_plan_membresia,
                    'id_tipo_servicio' => $tipo_servicio['id_tipo_servicio'],
                    'porcentaje_descuento' => $tipo_servicio['porcentaje_descuento'] ?? 100
                ]);
            }

            DB::commit();
            return $this->successResponse($plan_membresia, 'Plan de membresia creado correctamente');
        }
        catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al crear el plan: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $plan_membresia = PlanMembresiaService::getOne($id);
        if (!$plan_membresia) {
            return $this->errorResponse('Plan de membresia no encontrado', 404);
        }

        $plan_membresia->array_tipos_servicios = PlanMembresiaTipoServicioService::getOneByPlanMembresia($id);

        return $this->successResponse($plan_membresia, 'Plan de membresia obtenido correctamente');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'nullable|string|max:1000',
            'descripcion' => 'nullable|string|max:1000',
            'duracion_meses' => 'nullable|numeric',
            'precio' => 'nullable|numeric',
            'array_tipos_servicios' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'imagePath' => 'nullable|string|max:1000',
        ]);

        // validar que al menos un campo sea modificado
        if (!$request->has('nombre') && !$request->has('descripcion') && !$request->has('duracion_meses') && !$request->has('precio') && !$request->has('array_tipos_servicios') && !$request->has('image') && !$request->has('imagePath')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }

        $membresiasActivas = Membresia::where('id_plan_membresia', $id)
            ->where('estado', 'Activa')
            ->exists();

        if ($membresiasActivas) {
            return $this->errorResponse('No se puede editar este plan de membresía porque hay clientes que lo tienen activo actualmente', 400);
        }

        DB::beginTransaction();
        try {
            $data = $request->all();
            $plan_membresia = PlanMembresiaService::update($id, $data);
            if (!$plan_membresia) {
                DB::rollBack();
                return $this->errorResponse('Plan de membresia no encontrado', 404);
            }

            if ($request->has('array_tipos_servicios')) {
                $existentes = PlanMembresiaTipoServicioService::getOneByPlanMembresia($id);
                $idsExistentes = $existentes->pluck('id_tipo_servicio')->toArray();

                $serviciosNuevos = $request->array_tipos_servicios;
                $idsNuevos = array_column($serviciosNuevos, 'id_tipo_servicio');

                $idsPorEliminar = array_diff($idsExistentes, $idsNuevos);

                //borrar registros
                foreach ($idsPorEliminar as $idPorEliminar) {
                    PlanMembresiaTipoServicioService::deleteByPlanMembresiaAndTipoServicio($id, $idPorEliminar);
                }

                //insertar o actualizar registros
                foreach ($serviciosNuevos as $servicio) {
                    $idTS = $servicio['id_tipo_servicio'];
                    $porcentaje = $servicio['porcentaje_descuento'] ?? 100;

                    if (in_array($idTS, $idsExistentes)) {
                        // Actualizar porcentaje
                        $registro = $existentes->where('id_tipo_servicio', $idTS)->first();
                        PlanMembresiaTipoServicioService::update($registro->id_plan_membresia_tipo_servicio, [
                            'porcentaje_descuento' => $porcentaje
                        ]);
                    }
                    else {
                        // Crear nuevo
                        PlanMembresiaTipoServicioService::store([
                            'id_plan_membresia' => $id,
                            'id_tipo_servicio' => $idTS,
                            'porcentaje_descuento' => $porcentaje
                        ]);
                    }
                }
            }

            DB::commit();
            return $this->successResponse($plan_membresia, 'Plan de membresia actualizado correctamente');
        }
        catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al actualizar el plan: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        $membresiasActivas = Membresia::where('id_plan_membresia', $id)
            ->where('estado', 'Activa')
            ->exists();

        if ($membresiasActivas) {
            return $this->errorResponse('No se puede eliminar este plan de membresía porque hay clientes que lo tienen activo actualmente', 400);
        }

        $plan_membresia = PlanMembresiaService::delete($id);
        if (!$plan_membresia) {
            return $this->errorResponse('Plan de membresia no encontrado', 404);
        }
        return $this->successResponse($plan_membresia, 'Plan de membresia eliminado correctamente');
    }
}
