<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ServicioService;
use App\Services\ServicioMaterialService;
use App\Services\ServicioTipoEquipoService;
use App\Services\ServicioEspecialidadService;

class ServicioController extends Controller
{
    public function index()
    {
        $servicios = ServicioService::getAll();

        foreach ($servicios as $servicio) {
            $servicio->array_materiales = ServicioMaterialService::getOneByServicio($servicio->id_servicio);
            $servicio->array_tipos_equipos = ServicioTipoEquipoService::getOneByServicio($servicio->id_servicio);
            $servicio->array_especialidades = ServicioEspecialidadService::getOneByServicio($servicio->id_servicio);
        }

        return $this->successResponse(
            $servicios,
            $servicios->isEmpty() ? 'No se encontraron servicios' : 'Servicios obtenidos correctamente'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_tipo_servicio' => 'required|exists:tipos_servicios,id_tipo_servicio',
            'nombre' => 'required|string|max:1000',
            'descripcion' => 'required|string|max:1000',
            'unidad_medida' => 'required|string|max:100',
            'servicio_tabulado' => 'required|boolean',
            'precio_materiales' => 'nullable|numeric',
            'precio_tipos_equipos' => 'nullable|numeric',
            'precio_mano_obra' => 'nullable|numeric',
            'precio_general' => 'nullable|numeric',
            'duracion_horas' => 'nullable|numeric',
            'array_materiales' => 'nullable|array',
            'array_tipos_equipos' => 'nullable|array',
            'array_especialidades' => 'nullable|array',
        ]);

        $data = $request->all();

        $servicio = ServicioService::store($data);

        //guardar materiales si llegaron
        if ($request->has('array_materiales')) {
            foreach ($request->array_materiales as $material) {
                $data = [
                    'id_servicio' => $servicio->id_servicio,
                    'id_material' => $material['id_material'],
                    'cantidad' => $material['cantidad'],
                ];
                ServicioMaterialService::store($data);
            }
        }

        //guardar equipos si llegaron
        if ($request->has('array_tipos_equipos')) {
            foreach ($request->array_tipos_equipos as $tipo_equipo) {
                $data = [
                    'id_servicio' => $servicio->id_servicio,
                    'id_tipo_equipo' => $tipo_equipo['id_tipo_equipo'],
                    'cantidad' => $tipo_equipo['cantidad'],
                    'horas_uso' => $tipo_equipo['horas_uso'],
                ];
                ServicioTipoEquipoService::store($data);
            }
        }

        //guardar especialidades si llegaron
        if ($request->has('array_especialidades')) {
            foreach ($request->array_especialidades as $especialidad) {
                $data = [
                    'id_servicio' => $servicio->id_servicio,
                    'id_especialidad' => $especialidad['id_especialidad'],
                    'cantidad' => $especialidad['cantidad'],
                    'horas_hombre' => $especialidad['horas_hombre'],
                ];
                ServicioEspecialidadService::store($data);
            }
        }

        if (!$servicio) {
            return $this->errorResponse('No se pudo crear el servicio', 404);
        }
        return $this->successResponse($servicio, 'Servicio creado correctamente');
    }

    public function show($id)
    {
        $servicio = ServicioService::getOne($id);
        if (!$servicio) {
            return $this->errorResponse('Servicio no encontrado', 404);
        }

        $servicio->array_materiales = ServicioMaterialService::getOneByServicio($id);
        $servicio->array_tipos_equipos = ServicioTipoEquipoService::getOneByServicio($id);
        $servicio->array_especialidades = ServicioEspecialidadService::getOneByServicio($id);

        return $this->successResponse($servicio, 'Servicio obtenido correctamente');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_tipo_servicio' => 'exists:tipos_servicios,id_tipo_servicio',
            'nombre' => 'string|max:1000',
            'descripcion' => 'string|max:1000',
            'unidad_medida' => 'string|max:100',
            'servicio_tabulado' => 'boolean',
            'precio_materiales' => 'nullable|numeric',
            'precio_tipos_equipos' => 'nullable|numeric',
            'precio_mano_obra' => 'nullable|numeric',
            'precio_general' => 'nullable|numeric',
            'duracion_horas' => 'nullable|numeric',
            'is_deleted' => 'boolean',
            'array_materiales' => 'nullable|array',
            'array_tipos_equipos' => 'nullable|array',
            'array_especialidades' => 'nullable|array',
        ]);

        // validar que al menos un campo sea modificado
        if (!request()->has('id_tipo_servicio') && !request()->has('nombre') && !request()->has('descripcion') && !request()->has('unidad_medida') && !request()->has('servicio_tabulado') && !request()->has('precio_materiales') && !request()->has('precio_tipos_equipos') && !request()->has('precio_mano_obra') && !request()->has('precio_general') && !request()->has('duracion_horas') && !request()->has('is_deleted') && !request()->has('array_materiales') && !request()->has('array_tipos_equipos') && !request()->has('array_especialidades')) {
            return $this->errorResponse('Al menos un campo debe ser modificado', 400);
        }
        $data = $request->all();
        $servicio = ServicioService::update($id, $data);
        if (!$servicio) {
            return $this->errorResponse('Servicio no encontrado', 404);
        }

        if (!$servicio->servicio_tabulado) {
            // Si no es tabulado, eliminamos todos los registros asociados
            ServicioMaterialService::deleteByServicio($id);
            ServicioTipoEquipoService::deleteByServicio($id);
            ServicioEspecialidadService::deleteByServicio($id);
        } else {
            // Si es tabulado, sincronizamos los arreglos recibidos

            // --- MATERIALES ---
            if ($request->has('array_materiales')) {
                $existentes = ServicioMaterialService::getOneByServicio($id);
                $dictExistentes = $existentes->keyBy('id_material');
                $itemsNuevos = $request->array_materiales;

                foreach ($itemsNuevos as $item) {
                    $id_item = $item['id_material'];

                    //validacion de $dictExistentes->has($id_item) con foreach
                    // foreach ($existentes as $existente) {
                    //     if ($existente->id_material == $id_item) {
                    //         // Actualizar si existe
                    //         ServicioMaterialService::updateByServicioAndMaterial($id, $id_item, $item);
                    //         $dictExistentes->forget($id_item);
                    //     }
                    // }

                    if ($dictExistentes->has($id_item)) {
                        // Actualizar si existe
                        ServicioMaterialService::updateByServicioAndMaterial($id, $id_item, $item);
                        $dictExistentes->forget($id_item);
                    } else {
                        // Crear si es nuevo
                        $itemData = [
                            'id_servicio' => $id,
                            'id_material' => $item['id_material'],
                            'cantidad' => $item['cantidad'],
                        ];
                        ServicioMaterialService::store($itemData);
                    }
                }
                // Eliminar los que ya no están en el nuevo arreglo
                foreach ($dictExistentes as $sobrante) {
                    ServicioMaterialService::delete($sobrante->id_servicio_material);
                }
            }

            // --- TIPOS DE EQUIPOS ---
            if ($request->has('array_tipos_equipos')) {
                $existentes = ServicioTipoEquipoService::getOneByServicio($id);
                $dictExistentes = $existentes->keyBy('id_tipo_equipo');
                $itemsNuevos = $request->array_tipos_equipos;

                foreach ($itemsNuevos as $item) {
                    $id_item = $item['id_tipo_equipo'];
                    if ($dictExistentes->has($id_item)) {
                        // Actualizar
                        ServicioTipoEquipoService::updateByServicioAndTipoEquipo($id, $id_item, $item);
                        $dictExistentes->forget($id_item);
                    } else {
                        // Crear
                        $itemData = [
                            'id_servicio' => $id,
                            'id_tipo_equipo' => $item['id_tipo_equipo'],
                            'cantidad' => $item['cantidad'],
                            'horas_uso' => $item['horas_uso'],
                        ];
                        ServicioTipoEquipoService::store($itemData);
                    }
                }
                foreach ($dictExistentes as $sobrante) {
                    ServicioTipoEquipoService::delete($sobrante->id_servicio_tipo_equipo);
                }
            }

            // --- ESPECIALIDADES ---
            if ($request->has('array_especialidades')) {
                $existentes = ServicioEspecialidadService::getOneByServicio($id);
                $dictExistentes = $existentes->keyBy('id_especialidad');
                $itemsNuevos = $request->array_especialidades;

                foreach ($itemsNuevos as $item) {
                    $id_item = $item['id_especialidad'];
                    if ($dictExistentes->has($id_item)) {
                        // Actualizar
                        ServicioEspecialidadService::updateByServicioAndEspecialidad($id, $id_item, $item);
                        $dictExistentes->forget($id_item);
                    } else {
                        // Crear
                        $itemData = [
                            'id_servicio' => $id,
                            'id_especialidad' => $item['id_especialidad'],
                            'cantidad' => $item['cantidad'],
                            'horas_hombre' => $item['horas_hombre'],
                        ];
                        ServicioEspecialidadService::store($itemData);
                    }
                }
                foreach ($dictExistentes as $sobrante) {
                    ServicioEspecialidadService::delete($sobrante->id_servicio_especialidad);
                }
            }
        }

        return $this->successResponse($servicio, 'Servicio actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $servicio = ServicioService::delete($id);
        if (!$servicio) {
            return $this->errorResponse('Servicio no encontrado', 404);
        }
        return $this->successResponse($servicio, 'Servicio eliminado correctamente');
    }

    public function catalogoServicios()
    {
        $servicios = ServicioService::getAll();
        $catalogo = [];

        // crear catalogo por tipo de servicio
        // foreach ($servicios as $servicio) {
        //     $catalogo[$servicio->id_tipo_servicio][] = $servicio;
        // }

        foreach ($servicios as $servicio) {
            $id_tipo_servicio = $servicio->id_tipo_servicio;
            $tipo_servicio = $servicio->nombre_tipo_servicio;

            if (!isset($catalogo[$id_tipo_servicio])) {
                $catalogo[$id_tipo_servicio] = [
                    'id_tipo_servicio' => $id_tipo_servicio,
                    'tipo_servicio' => $tipo_servicio,
                    'servicios' => []
                ];
            }

            unset($servicio->id_tipo_servicio);
            unset($servicio->nombre_tipo_servicio);
            $catalogo[$id_tipo_servicio]['servicios'][] = $servicio;
        }

        //dd($catalogo);

        return $this->successResponse($catalogo, 'Servicios obtenidos correctamente');
    }
}
