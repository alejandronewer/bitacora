<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntradaEventoDetectado extends Model
{
    protected $table = 'entrada_eventos_detectados';

    protected $primaryKey = 'entrada_id';
    public $incrementing = false;

    protected $fillable = [
        'entrada_id',
        'tipo_evento',
        'detalle',
    ];

    public function entrada()
    {
        return $this->belongsTo(EntradaBitacora::class, 'entrada_id');
    }

    
}
