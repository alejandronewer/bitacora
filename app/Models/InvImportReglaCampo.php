<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvImportReglaCampo extends Model
{
    protected $table = 'inv_import_regla_campos';

    protected $fillable = [
        'regla_id',
        'columna_fuente',
        'campo_destino',
        'transformacion',
        'por_defecto',
        'es_clave_upsert',
        'orden',
        'activo',
    ];

    protected $casts = [
        'es_clave_upsert' => 'boolean',
        'activo' => 'boolean',
    ];

    public function regla()
    {
        return $this->belongsTo(InvImportRegla::class, 'regla_id');
    }
}
