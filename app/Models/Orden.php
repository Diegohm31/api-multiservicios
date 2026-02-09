<?php

namespace App\Models;

use App\Models\ApiModel;

class Orden extends ApiModel
{
    const PADDING = 5;
    protected $table = 'ordenes';
    protected $primaryKey = 'id_orden';
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
        'id_cliente',
        'id_admin',
        'id_presupuesto',
        'direccion',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'fecha_inicio_real',
        'fecha_fin_real',
        'fecha_emision',
        'fecha_validacion',
        'observaciones',
        'calificacion',
    ];
}

