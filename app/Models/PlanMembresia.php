<?php

namespace App\Models;

use App\Models\ApiModel;

class PlanMembresia extends ApiModel
{
    const PADDING = 5;
    const IMAGE_PATH = 'planes_membresias';
    const IMAGE_FIELD = 'image'; // campo que guarda el nombre original de la imagen
    const IMAGE_PATH_FIELD = 'imagePath'; // campo que guarda la ruta relativa de la imagen
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
        'is_deleted',
        'image',
        'imagePath'
    ];
}
