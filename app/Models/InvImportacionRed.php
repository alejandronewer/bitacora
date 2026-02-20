<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvImportacionRed extends Model
{
    protected $table = 'inv_importaciones_redes';

    protected $fillable = [
        'red_id',
        'regla_id',
        'usuario_id',
        'archivo_nombre',
        'fuente',
        'hash_archivo',
        'estado',
        'total_registros',
        'procesados',
        'creados',
        'actualizados',
        'marcados_baja',
    ];

    public function red()
    {
        return $this->belongsTo(InvRed::class, 'red_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function regla()
    {
        return $this->belongsTo(InvImportRegla::class, 'regla_id');
    }

    public function errores()
    {
        return $this->hasMany(InvImportError::class, 'importacion_id');
    }
}
