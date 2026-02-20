<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SistemaExterno extends Model
{
    protected $table = 'sistemas_externos';

    protected $fillable = [
        'codigo',
        'nombre',
        'patron_regex',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function referenciasExternas()
    {
        return $this->hasMany(ReferenciaExterna::class, 'sistema_externo_id');
    }
}
