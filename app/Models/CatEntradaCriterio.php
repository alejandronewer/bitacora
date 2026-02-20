<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatEntradaCriterio extends Model
{
    protected $table = 'cat_entrada_criterio';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function entradas()
    {
        return $this->hasMany(EntradaBitacora::class, 'entrada_criterio_id');
    }
}
