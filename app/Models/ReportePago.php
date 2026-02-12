<?php

namespace App\Models;

use App\Models\ApiModel;

class ReportePago extends ApiModel
{
    const PADDING = 5;
    const IMAGE_PATH = 'reportes_pagos';
    const IMAGE_FIELD = 'image'; // campo que guarda el nombre original de la imagen
    const IMAGE_PATH_FIELD = 'imagePath'; // campo que guarda la ruta relativa de la imagen

    protected $table = 'reportes_pagos';
    protected $primaryKey = 'id_reporte_pago';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    // (Lista blanca): Especificas qué campos SI se pueden guardar masivamente
    protected $fillable = [
        'id_cliente',
        'id_admin',
        'id_orden',
        'id_plan_membresia',
        'monto',
        'metodo_pago',
        'num_referencia',
        'image',
        'imagePath',
        'estado',
        'fecha_emision',
        'fecha_validacion',
        'observaciones',
    ];
}
