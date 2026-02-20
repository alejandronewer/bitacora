<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmMatrizOrdenActividad extends Model
{
    protected $table = 'pm_matriz_orden_actividad';

    protected $fillable = [
        'pm_clase_orden_id',
        'pm_clase_actividad_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function claseOrden()
    {
        return $this->belongsTo(PmClaseOrden::class, 'pm_clase_orden_id');
    }

    public function claseActividad()
    {
        return $this->belongsTo(PmClaseActividad::class, 'pm_clase_actividad_id');
    }
}
