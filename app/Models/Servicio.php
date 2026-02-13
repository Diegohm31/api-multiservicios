<?php

namespace App\Models;

use App\Models\ApiModel;

class Servicio extends ApiModel
{
    const PADDING = 5;
    const IMAGE_PATH = 'servicios';
    const IMAGE_FIELD = 'image'; // campo que guarda el nombre original de la imagen
    const IMAGE_PATH_FIELD = 'imagePath'; // campo que guarda la ruta relativa de la imagen
    protected $table = 'servicios';
    protected $primaryKey = 'id_servicio';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    // (Lista negra): Especificas qué campos NO se pueden guardar masivamente
    // Al poner protected $guarded = []; (un array vacío), 
    // le estás diciendo a Laravel: "No protejas ningún campo, permite que TODOS se puedan guardar masivamente".

    // Ventaja:
    // Es muy cómodo porque no tienes que estar agregando campos al $fillable cada 
    // vez que añades una columna a la base de datos.

    //protected $guarded = [];

    protected $hidden = [
    ];

    // (Lista blanca): Especificas qué campos SI se pueden guardar masivamente
    protected $fillable = [
        'id_tipo_servicio',
        'nombre',
        'descripcion',
        'unidad_medida',
        'servicio_tabulado',
        'precio_materiales',
        'precio_tipos_equipos',
        'precio_mano_obra',
        'precio_general',
        'duracion_horas',
        'is_deleted',
        'image',
        'imagePath',
    ];
}

