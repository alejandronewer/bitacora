<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Requests\Api\Admin\StoreConfiguracionUsuarioRequest;
use App\Http\Requests\Api\Admin\UpdateConfiguracionUsuarioRequest;
use App\Http\Resources\ConfiguracionUsuarioResource;
use App\Models\ConfiguracionUsuario;
use Illuminate\Support\Facades\Hash;

class ConfiguracionUsuarioController extends Controller
{
    public function index()
    {
        return ConfiguracionUsuarioResource::collection(ConfiguracionUsuario::where('usuario_id', auth()->id())
            ->orderBy('clave')
            ->get());
    }

    public function store(StoreConfiguracionUsuarioRequest $request)
    {
        return new ConfiguracionUsuarioResource(ConfiguracionUsuario::create(array_merge($request->validated(), [
            'usuario_id' => auth()->id(),
        ])));
    }

    public function update(UpdateConfiguracionUsuarioRequest $request, ConfiguracionUsuario $configuracion)
    {
        if ($configuracion->usuario_id !== auth()->id()) {
            abort(403);
        }

        $configuracion->fill($request->validated())->save();
        return new ConfiguracionUsuarioResource($configuracion->refresh());
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make((string) $request->input('password')),
        ])->save();

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }
}
