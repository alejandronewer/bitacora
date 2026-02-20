<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvDetalleTunel extends Model
{
    protected $table = 'inv_detalle_tuneles';

    protected $fillable = [
        'elemento_red_id',
        'instancia_tunel_id',
        'user_label',
        'cliente',
        'tipo_tunel',
        'ethvpn_id',
    ];

    public function elemento()
    {
        return $this->belongsTo(InvElementoRed::class, 'elemento_red_id');
    }
}
