<?php

namespace App\Models;

use App\Models\ApiModel;

class OrdenServicioEquipo extends ApiModel
{
    const PADDING = 5;
    protected $table = 'ordenes_servicios_equipos';
    protected $primaryKey = 'id_orden_servicio_equipo';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_orden_servicio',
        'id_equipo',
        'fecha_inicio',
        'fecha_fin',
    ];
}
