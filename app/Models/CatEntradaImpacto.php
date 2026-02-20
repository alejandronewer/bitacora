<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatEntradaImpacto extends Model
{
    protected $table = 'cat_entrada_impacto';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'severidad',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function entradas()
    {
        return $this->hasMany(EntradaBitacora::class, 'entrada_impacto_id');
    }
}
