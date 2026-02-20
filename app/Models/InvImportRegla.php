<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvImportRegla extends Model
{
    protected $table = 'inv_import_reglas';

    protected $fillable = [
        'red_id',
        'nombre',
        'tipo_elemento',
        'tabla_destino',
        'archivo_patron',
        'delimitador',
        'usa_comillas',
        'tiene_encabezado',
        'encoding',
        'activo',
    ];

    protected $casts = [
        'usa_comillas' => 'boolean',
        'tiene_encabezado' => 'boolean',
        'activo' => 'boolean',
    ];

    public function red()
    {
        return $this->belongsTo(InvRed::class, 'red_id');
    }

    public function campos()
    {
        return $this->hasMany(InvImportReglaCampo::class, 'regla_id')->orderBy('orden');
    }

    public function importaciones()
    {
        return $this->hasMany(InvImportacionRed::class, 'regla_id');
    }
}
