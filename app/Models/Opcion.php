<?php

namespace App\Models;

use App\Models\ApiModel;

class Opcion extends ApiModel
{
    const PADDING = 5;
    protected $table = 'opciones';
    protected $primaryKey = 'id_opcion';
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
        'nombre',
        'ruta',
        'icono_path',
        'es_categoria',
        'id_padre',
    ];
}

