<?php

namespace App\Models;

use App\Models\ApiModel;

class OrdenServicioOperativo extends ApiModel
{
    const PADDING = 5;
    protected $table = 'ordenes_servicios_operativos';
    protected $primaryKey = 'id_orden_servicio_operativo';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_orden_servicio',
        'id_operativo',
        'id_especialidad',
        'fecha_inicio',
        'fecha_fin',
        'es_jefe',
    ];
}
