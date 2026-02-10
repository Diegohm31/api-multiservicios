<?php

namespace App\Models;

use App\Models\ApiModel;

class Presupuesto extends ApiModel
{
    const PADDING = 5;
    protected $table = 'presupuestos';
    protected $primaryKey = 'id_presupuesto';
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
        'id_admin',
        'total_materiales',
        'total_equipos',
        'total_mano_obra',
        'total_general',
        'total_descuento',
        'total_a_pagar',
        'estado',
        'fecha_emision',
    ];
}

