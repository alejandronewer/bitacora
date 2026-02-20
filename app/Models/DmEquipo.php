<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DmEquipo extends Model
{
    protected $table = 'dm_equipos';

    protected $fillable = [
        'codigo',
        'nombre',
        'ubicacion_tecnica_id',
        // Legacy (mientras se completa la transición)
        'fabricante',
        'modelo',
        'numero_serie',
        // Nuevo esquema base
        'area',
        'activo',
        'fuente',
        'last_sync_at',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    public function ubicacionTecnica()
    {
        return $this->belongsTo(DmUbicacionTecnica::class, 'ubicacion_tecnica_id');
    }

    public function entradasDirectas()
    {
        return $this->hasMany(EntradaBitacora::class, 'equipo_id');
    }

    public function entradasPivot()
    {
        return $this->belongsToMany(
            EntradaBitacora::class,
            'entrada_equipos',
            'equipo_id',
            'entrada_id'
        );
    }
}
