<?php

namespace App\Models;

use App\Models\ApiModel;

class PlanMembresia extends ApiModel
{
    const PADDING = 5;

    protected $table = 'planes_membresias';
    protected $primaryKey = 'id_plan_membresia';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    // (Lista blanca): Especificas qué campos SI se pueden guardar masivamente
    protected $fillable = [
        'nombre',
        'descripcion',
        'duracion_meses',
        'precio',
        'estado',
    ];
}
