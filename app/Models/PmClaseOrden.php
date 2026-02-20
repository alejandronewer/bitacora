<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmClaseOrden extends Model
{
    protected $table = 'pm_clase_orden';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function matrizOrdenes()
    {
        return $this->hasMany(PmMatrizOrdenActividad::class, 'pm_clase_orden_id');
    }

    public function entradas()
    {
        return $this->hasMany(EntradaBitacora::class, 'pm_clase_orden_id');
    }
}
