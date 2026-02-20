<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenciaExterna extends Model
{
    protected $table = 'referencias_externas';

    protected $fillable = [
        'entrada_id',
        'sistema_externo_id',
        'externo_id',
    ];

    public function entrada()
    {
        return $this->belongsTo(EntradaBitacora::class, 'entrada_id');
    }

    public function sistema()
    {
        return $this->belongsTo(SistemaExterno::class, 'sistema_externo_id');
    }
}
