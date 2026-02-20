<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'correo',
        'password',
        'activo',
        'rpe',
        'rtt',
        'estatus_actual',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function configuraciones()
    {
        return $this->hasMany(ConfiguracionUsuario::class, 'usuario_id');
    }

    public function entradas()
    {
        return $this->hasMany(EntradaBitacora::class, 'usuario_id');
    }

    public function adjuntos()
    {
        return $this->hasMany(Adjunto::class, 'usuario_id');
    }

    public function importacionesRed()
    {
        return $this->hasMany(InvImportacionRed::class, 'usuario_id');
    }
}
