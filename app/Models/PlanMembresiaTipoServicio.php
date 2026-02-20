<?php

namespace App\Models;

use App\Models\ApiModel;

class PlanMembresiaTipoServicio extends ApiModel
{
    const PADDING = 5;

    protected $table = 'planes_membresias_tipos_servicios';
    protected $primaryKey = 'id_plan_membresia_tipo_servicio';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    // (Lista blanca): Especificas qué campos SI se pueden guardar masivamente
    protected $fillable = [
        'id_plan_membresia',
        'id_tipo_servicio',
        'porcentaje_descuento',
    ];
}
