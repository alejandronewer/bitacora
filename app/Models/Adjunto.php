<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adjunto extends Model
{
    protected $table = 'adjuntos';

    protected $fillable = [
        'entrada_id',
        'usuario_id',
        'tipo',
        'nombre_original',
        'mime_original',
        'tamano_bytes_original',
        'extension_final',
        'mime_final',
        'tamano_bytes_final',
        'ancho',
        'alto',
        'ruta',
        'sha256',
    ];

    public function entrada()
    {
        return $this->belongsTo(EntradaBitacora::class, 'entrada_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
