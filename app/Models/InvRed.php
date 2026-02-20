<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvRed extends Model
{
    protected $table = 'inv_redes';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function importaciones()
    {
        return $this->hasMany(InvImportacionRed::class, 'red_id');
    }

    public function importReglas()
    {
        return $this->hasMany(InvImportRegla::class, 'red_id');
    }

    public function elementos()
    {
        return $this->hasMany(InvElementoRed::class, 'red_id');
    }

    public function dominios()
    {
        return $this->belongsToMany(InvDominio::class, 'inv_red_dominios', 'red_id', 'dominio_id');
    }
}
