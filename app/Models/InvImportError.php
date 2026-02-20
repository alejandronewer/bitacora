<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvImportError extends Model
{
    protected $table = 'inv_import_errores';

    const UPDATED_AT = null;

    protected $fillable = [
        'importacion_id',
        'fila_numero',
        'campo',
        'valor',
        'mensaje',
        'created_at',
    ];

    public function importacion()
    {
        return $this->belongsTo(InvImportacionRed::class, 'importacion_id');
    }
}
