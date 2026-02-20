<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EntradaBitacora extends Model
{
    protected $table = 'entradas_bitacora';

    protected $fillable = [
        'titulo',
        'cuerpo_html',
        'cuerpo_texto',
        'resumen_tecnico',
        'fecha_inicio',
        'fecha_fin',
        'usuario_id',
        'ubicacion_tecnica_id',
        'equipo_id',
        'ubicacion_manual',
        'equipo_manual',
        'entrada_criterio_id',
        'entrada_impacto_id',
        'pm_clase_orden_id',
        'pm_clase_actividad_id',
        'publicado',
        'publicado_at',
        'tipo_registro',
        'accion_inventario',
    ];

    protected $casts = [
        'publicado' => 'boolean',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'publicado_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function ubicacionTecnica()
    {
        return $this->belongsTo(DmUbicacionTecnica::class, 'ubicacion_tecnica_id');
    }

    public function equipo()
    {
        return $this->belongsTo(DmEquipo::class, 'equipo_id');
    }

    public function criterio()
    {
        return $this->belongsTo(CatEntradaCriterio::class, 'entrada_criterio_id');
    }

    public function impacto()
    {
        return $this->belongsTo(CatEntradaImpacto::class, 'entrada_impacto_id');
    }

    public function pmClaseOrden()
    {
        return $this->belongsTo(PmClaseOrden::class, 'pm_clase_orden_id');
    }

    public function pmClaseActividad()
    {
        return $this->belongsTo(PmClaseActividad::class, 'pm_clase_actividad_id');
    }

    public function adjuntos()
    {
        return $this->hasMany(Adjunto::class, 'entrada_id');
    }

    public function referenciasExternas()
    {
        return $this->hasMany(ReferenciaExterna::class, 'entrada_id');
    }

    public function eventoDetectado()
    {
        return $this->hasOne(EntradaEventoDetectado::class, 'entrada_id');
    }

    public function ubicaciones()
    {
        return $this->belongsToMany(
            DmUbicacionTecnica::class,
            'entrada_ubicaciones',
            'entrada_id',
            'ubicacion_tecnica_id'
        );
    }

    public function equipos()
    {
        return $this->belongsToMany(
            DmEquipo::class,
            'entrada_equipos',
            'entrada_id',
            'equipo_id'
        );
    }

    public function inventarioElementos()
    {
        return $this->belongsToMany(
            InvElementoRed::class,
            'inv_entrada_elementos_red',
            'entrada_id',
            'elemento_red_id'
        );
    }

    public function scopePublicadas(Builder $query): Builder
    {
        return $query->where('publicado', 1);
    }

    public function scopePorUsuario(Builder $query, int $usuarioId): Builder
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeEnRango(Builder $query, $desde, $hasta): Builder
    {
        return $query->whereBetween('fecha_inicio', [$desde, $hasta]);
    }
}
