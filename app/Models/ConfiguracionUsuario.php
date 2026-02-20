<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionUsuario extends Model
{
    protected $table = 'configuracion_usuario';

    protected $fillable = [
        'usuario_id',
        'clave',
        'valor',
        'tipo',
        'descripcion',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
