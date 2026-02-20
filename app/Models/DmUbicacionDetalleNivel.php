<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DmUbicacionDetalleNivel extends Model
{
    protected $table = 'dm_ubicacion_detalle_nivel';

    protected $fillable = [
        'nivel',
        'codigo',
        'nombre',
        'descripcion',
        'rama_nivel_3',
        'activo',
        'origen',
    ];

    protected $casts = [
        'nivel' => 'integer',
        'activo' => 'boolean',
    ];
}
