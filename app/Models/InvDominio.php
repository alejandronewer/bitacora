<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvDominio extends Model
{
    protected $table = 'inv_dominios';

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

    public function redes()
    {
        return $this->belongsToMany(InvRed::class, 'inv_red_dominios', 'dominio_id', 'red_id');
    }
}
