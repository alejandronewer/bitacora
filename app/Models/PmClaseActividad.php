<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmClaseActividad extends Model
{
    protected $table = 'pm_clase_actividad';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function matrizActividades()
    {
        return $this->hasMany(PmMatrizOrdenActividad::class, 'pm_clase_actividad_id');
    }

    public function entradas()
    {
        return $this->hasMany(EntradaBitacora::class, 'pm_clase_actividad_id');
    }
}
