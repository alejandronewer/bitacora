<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvDetalleNodo extends Model
{
    protected $table = 'inv_detalle_nodos';

    protected $fillable = [
        'elemento_red_id',
        'ne_id',
        'ne_dbid',
        'dn_externo',
        'native_name',
        'user_label',
        'nombre_producto',
        'tipo_equipo',
        'version_me',
        'direccion_red',
        'nombre_grupo',
    ];

    public function elemento()
    {
        return $this->belongsTo(InvElementoRed::class, 'elemento_red_id');
    }
}
