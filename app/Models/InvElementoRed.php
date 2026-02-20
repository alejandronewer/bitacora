<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvElementoRed extends Model
{
    protected $table = 'inv_elementos_redes';

    protected $fillable = [
        'red_id',
        'tipo',
        'codigo',
        'nombre',
        'estado',
        'fecha_alta',
        'fecha_baja',
        'updated_at_fuente',
        'origen',
        'observaciones',
        'last_seen_importacion_id',
    ];

    protected $casts = [
        'fecha_alta' => 'datetime',
        'fecha_baja' => 'datetime',
        'updated_at_fuente' => 'datetime',
    ];

    public function red()
    {
        return $this->belongsTo(InvRed::class, 'red_id');
    }

    public function lastImportacion()
    {
        return $this->belongsTo(InvImportacionRed::class, 'last_seen_importacion_id');
    }

    public function detalleNodo()
    {
        return $this->hasOne(InvDetalleNodo::class, 'elemento_red_id');
    }

    public function detalleServicio()
    {
        return $this->hasOne(InvDetalleServicio::class, 'elemento_red_id');
    }

    public function detalleEnlace()
    {
        return $this->hasOne(InvDetalleEnlace::class, 'elemento_red_id');
    }

    public function detalleTunel()
    {
        return $this->hasOne(InvDetalleTunel::class, 'elemento_red_id');
    }

    public function entradas()
    {
        return $this->belongsToMany(
            EntradaBitacora::class,
            'inv_entrada_elementos_red',
            'elemento_red_id',
            'entrada_id'
        );
    }
}
