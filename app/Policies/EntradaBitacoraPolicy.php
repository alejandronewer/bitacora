<?php

namespace App\Policies;

use App\Models\EntradaBitacora;
use App\Models\User;

class EntradaBitacoraPolicy
{
    public function view(?User $user, EntradaBitacora $entrada): bool
    {
        if ($entrada->publicado) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['operador', 'administrador', 'invitado']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['operador', 'administrador']);
    }

    public function update(User $user, EntradaBitacora $entrada): bool
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        return $user->hasRole('operador') && $entrada->usuario_id === $user->id;
    }

    public function delete(User $user, EntradaBitacora $entrada): bool
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        return $user->hasRole('operador') && $entrada->usuario_id === $user->id;
    }

    public function publish(User $user, EntradaBitacora $entrada): bool
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        return $user->hasRole('operador') && $entrada->usuario_id === $user->id;
    }

    public function close(User $user, EntradaBitacora $entrada): bool
    {
        if ($user->hasRole('administrador')) {
            return true;
        }

        return $user->hasRole('operador') && $entrada->usuario_id === $user->id;
    }
}
