<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\UpdateConfiguracionSistemaRequest;
use App\Http\Resources\ConfiguracionSistemaResource;
use App\Models\ConfiguracionSistema;

class ConfiguracionSistemaController extends Controller
{
    public function index()
    {
        $query = ConfiguracionSistema::orderBy('clave');
        $user = auth()->user();

        if (! $user || ! $user->hasRole('administrador')) {
            $publicKeys = [
                'app_nombre',
                'app_siglas',
                'timezone',
                'bitacora_publica',
                'max_adjuntos_por_entrada',
                'paginacion.default_per_page',
            ];
            $publicPrefixes = ['tema.', 'enlaces.', 'imagenes.', 'paginacion.', 'archivos.'];

            $query->where(function ($builder) use ($publicKeys, $publicPrefixes) {
                $builder->whereIn('clave', $publicKeys);
                foreach ($publicPrefixes as $prefix) {
                    $builder->orWhere('clave', 'like', $prefix.'%');
                }
            });
        }

        return ConfiguracionSistemaResource::collection($query->get());
    }

    public function update(UpdateConfiguracionSistemaRequest $request, ConfiguracionSistema $configuracion)
    {
        $configuracion->fill($request->validated())->save();
        return new ConfiguracionSistemaResource($configuracion->refresh());
    }
}
