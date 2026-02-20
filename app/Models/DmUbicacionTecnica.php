<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DmUbicacionTecnica extends Model
{
    protected $table = 'dm_ubicaciones_tecnicas';

    protected $fillable = [
        'codigo',
        'nombre',
        'nivel_1',
        'nivel_2',
        'nivel_3',
        'nivel_4',
        'nivel_5',
        'nivel_6',
        'nivel_7',
        'nivel_8',
        'activo',
        'fuente',
        'last_sync_at',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    public function equipos()
    {
        return $this->hasMany(DmEquipo::class, 'ubicacion_tecnica_id');
    }

    public function entradasDirectas()
    {
        return $this->hasMany(EntradaBitacora::class, 'ubicacion_tecnica_id');
    }

    public function entradasPivot()
    {
        return $this->belongsToMany(
            EntradaBitacora::class,
            'entrada_ubicaciones',
            'ubicacion_tecnica_id',
            'entrada_id'
        );
    }
}
