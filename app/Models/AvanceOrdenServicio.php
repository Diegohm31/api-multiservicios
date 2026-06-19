<?php

namespace App\Models;

use App\Models\ApiModel;

class AvanceOrdenServicio extends ApiModel
{
    const PADDING = 5;
    const IMAGE_PATH = 'avances_ordenes_servicios';
    const IMAGE_FIELD = 'image'; // campo que guarda el nombre original de la imagen
    const IMAGE_PATH_FIELD = 'imagePath'; // campo que guarda la ruta relativa de la imagen
    protected $table = 'avances_ordenes_servicios';
    protected $primaryKey = 'id_avance_orden_servicio';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $hidden = [
    ];

    // (Lista blanca): Especificas qué campos SI se pueden guardar masivamente
    protected $fillable = [
        'id_orden_servicio',
        'id_operativo',
        'descripcion',
        'porcentaje_avance',
        'fecha_avance',
        'image',
        'imagePath',
        'is_deleted'
    ];
}
