<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvDetalleEnlace extends Model
{
    protected $table = 'inv_detalle_enlaces';

    protected $fillable = [
        'elemento_red_id',
        'instancia_enlace_id',
        'motlink_label',
        'trail_id',
        'nodo_a_ne_id',
        'nodo_z_ne_id',
    ];

    public function elemento()
    {
        return $this->belongsTo(InvElementoRed::class, 'elemento_red_id');
    }
}
