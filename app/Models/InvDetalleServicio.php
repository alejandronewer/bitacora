<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvDetalleServicio extends Model
{
    protected $table = 'inv_detalle_servicios';

    protected $fillable = [
        'elemento_red_id',
        'instancia_servicio_id',
        'user_label',
        'cliente',
        'tipo_servicio',
        'ethvpn_id',
    ];

    public function elemento()
    {
        return $this->belongsTo(InvElementoRed::class, 'elemento_red_id');
    }
}
