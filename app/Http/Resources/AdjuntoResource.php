<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class AdjuntoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $url = null;
        if ($this->ruta) {
            $ruta = ltrim($this->ruta, '/');
            $path = Str::startsWith($ruta, 'storage/')
                ? "/{$ruta}"
                : "/storage/{$ruta}";
            $url = $request->getSchemeAndHttpHost() . $path;
        }

        return [
            'id' => $this->id,
            'entrada_id' => $this->entrada_id,
            'usuario_id' => $this->usuario_id,
            'tipo' => $this->tipo,
            'nombre_original' => $this->nombre_original,
            'mime_original' => $this->mime_original,
            'tamano_bytes_original' => $this->tamano_bytes_original,
            'extension_final' => $this->extension_final,
            'mime_final' => $this->mime_final,
            'tamano_bytes_final' => $this->tamano_bytes_final,
            'ancho' => $this->ancho,
            'alto' => $this->alto,
            'ruta' => $this->ruta,
            'url' => $url,
            'sha256' => $this->sha256,
            'created_at' => $this->created_at,
        ];
    }
}
