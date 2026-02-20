<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreUserRequest;
use App\Http\Requests\Api\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Adjunto;
use App\Models\ConfiguracionUsuario;
use App\Models\EntradaBitacora;
use App\Models\InvImportacionRed;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function activeAdminCountExcluding(?int $userId = null): int
    {
        $query = User::role('administrador')->where('activo', 1);
        if ($userId) {
            $query->where('id', '!=', $userId);
        }
        return $query->count();
    }

    public function index()
    {
        return UserResource::collection(
            User::with('roles')
                ->withCount(['entradas', 'adjuntos', 'importacionesRed', 'configuraciones'])
                ->orderBy('nombre')
                ->get()
        );
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        if (! empty($roles)) {
            $user->syncRoles($roles);
        }

        return new UserResource($user->load('roles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        $currentRoles = $user->roles->pluck('name')->toArray();
        $targetRoles = is_array($roles) ? $roles : $currentRoles;
        $willBeAdmin = in_array('administrador', $targetRoles, true);
        $willBeActive = array_key_exists('activo', $data) ? (bool) $data['activo'] : (bool) $user->activo;

        $isCurrentActiveAdmin = $user->hasRole('administrador') && $user->activo;
        if ($isCurrentActiveAdmin && (! $willBeAdmin || ! $willBeActive) && $this->activeAdminCountExcluding($user->id) === 0) {
            return response()->json(['message' => 'Debe existir al menos un administrador activo.'], 422);
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->fill($data)->save();

        if (is_array($roles)) {
            $user->syncRoles($roles);
        }

        return new UserResource($user->load('roles'));
    }

    public function destroy(User $user)
    {
        $isAdmin = $user->hasRole('administrador') && $user->activo;
        if ($isAdmin && $this->activeAdminCountExcluding($user->id) === 0) {
            return response()->json(['message' => 'No se puede eliminar el último administrador activo.'], 422);
        }

        $enUso = EntradaBitacora::where('usuario_id', $user->id)->exists()
            || Adjunto::where('usuario_id', $user->id)->exists()
            || InvImportacionRed::where('usuario_id', $user->id)->exists()
            || ConfiguracionUsuario::where('usuario_id', $user->id)->exists();

        if ($enUso) {
            return response()->json(['message' => 'No se puede eliminar porque tiene historial.'], 422);
        }

        $user->delete();
        return response()->noContent();
    }
}
