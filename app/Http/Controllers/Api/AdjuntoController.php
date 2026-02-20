<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAdjuntoArchivoRequest;
use App\Http\Requests\Api\StoreAdjuntoImagenRequest;
use App\Http\Resources\AdjuntoResource;
use App\Models\Adjunto;
use App\Models\ConfiguracionSistema;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdjuntoController extends Controller
{
    public function storeImagen(StoreAdjuntoImagenRequest $request)
    {
        $file = $request->file('file');
        $adjunto = $this->guardarAdjunto($file, 'imagen', $request->user()->id, null);

        return new AdjuntoResource($adjunto);
    }

    public function storeArchivo(StoreAdjuntoArchivoRequest $request)
    {
        $file = $request->file('file');
        $adjunto = $this->guardarAdjunto($file, 'archivo', $request->user()->id, null);

        return new AdjuntoResource($adjunto);
    }

    public function destroy(Request $request, Adjunto $adjunto)
    {
        if ($adjunto->entrada) {
            $this->authorize('update', $adjunto->entrada);
        } else {
            if (! $request->user() || (int) $request->user()->id !== (int) $adjunto->usuario_id) {
                abort(403);
            }
        }

        if ($adjunto->ruta) {
            Storage::disk('public')->delete($adjunto->ruta);
        }

        $adjunto->delete();

        return response()->noContent();
    }

    private function guardarAdjunto(UploadedFile $file, string $tipo, int $usuarioId, ?int $entradaId): Adjunto
    {
        if ($tipo === 'imagen') {
            return $this->guardarImagen($file, $usuarioId, $entradaId);
        }

        $uuid = (string) Str::uuid();
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin');
        $dir = 'adjuntos/archivos/' . now()->format('Y/m');
        $path = "{$dir}/{$uuid}.{$extension}";
        Storage::disk('public')->putFileAs($dir, $file, "{$uuid}.{$extension}");

        return Adjunto::create([
            'entrada_id' => $entradaId,
            'usuario_id' => $usuarioId,
            'tipo' => 'archivo',
            'nombre_original' => $file->getClientOriginalName(),
            'mime_original' => $file->getClientMimeType(),
            'tamano_bytes_original' => $file->getSize(),
            'extension_final' => $extension,
            'mime_final' => $file->getClientMimeType(),
            'tamano_bytes_final' => $file->getSize(),
            'ruta' => $path,
            'sha256' => hash_file('sha256', $file->getPathname()),
        ]);
    }

    private function guardarImagen(UploadedFile $file, int $usuarioId, ?int $entradaId): Adjunto
    {
        if (! function_exists('imagecreatefromstring')) {
            abort(500, 'La extensión GD no está disponible en el servidor.');
        }

        $config = $this->getImagenConfig();
        $maxBytes = $config['max_mb'] * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            abort(422, 'La imagen supera el tamaño máximo permitido.');
        }

        $binary = file_get_contents($file->getPathname());
        $image = @imagecreatefromstring($binary);
        if (! $image) {
            abort(422, 'Archivo de imagen inválido.');
        }

        $origW = imagesx($image);
        $origH = imagesy($image);
        $targetW = $origW;
        $targetH = $origH;

        if (($config['max_ancho'] && $origW > $config['max_ancho']) || ($config['max_alto'] && $origH > $config['max_alto'])) {
            $ratioW = $config['max_ancho'] ? $config['max_ancho'] / $origW : 1;
            $ratioH = $config['max_alto'] ? $config['max_alto'] / $origH : 1;
            $ratio = min($ratioW, $ratioH);
            $targetW = max(1, (int) round($origW * $ratio));
            $targetH = max(1, (int) round($origH * $ratio));

            $resized = imagecreatetruecolor($targetW, $targetH);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetW, $targetH, $origW, $origH);
            imagedestroy($image);
            $image = $resized;
        }

        $compression = $this->toPngCompression($config['calidad_png']);
        ob_start();
        imagepng($image, null, $compression);
        $pngBinary = ob_get_clean();
        imagedestroy($image);

        $uuid = (string) Str::uuid();
        $dir = 'adjuntos/imagenes/' . now()->format('Y/m');
        $path = "{$dir}/{$uuid}.png";
        Storage::disk('public')->put($path, $pngBinary);

        return Adjunto::create([
            'entrada_id' => $entradaId,
            'usuario_id' => $usuarioId,
            'tipo' => 'imagen',
            'nombre_original' => $file->getClientOriginalName(),
            'mime_original' => $file->getClientMimeType(),
            'tamano_bytes_original' => $file->getSize(),
            'extension_final' => 'png',
            'mime_final' => 'image/png',
            'tamano_bytes_final' => strlen($pngBinary),
            'ancho' => $targetW,
            'alto' => $targetH,
            'ruta' => $path,
            'sha256' => hash('sha256', $pngBinary),
        ]);
    }

    private function getImagenConfig(): array
    {
        $defaults = [
            'max_mb' => 2,
            'max_ancho' => 1920,
            'max_alto' => 1080,
            'calidad_png' => 90,
        ];

        $config = ConfiguracionSistema::whereIn('clave', [
            'imagenes.max_mb',
            'imagenes.max_ancho',
            'imagenes.max_alto',
            'imagenes.calidad_png',
        ])->pluck('valor', 'clave');

        return [
            'max_mb' => (int) ($config['imagenes.max_mb'] ?? $defaults['max_mb']),
            'max_ancho' => (int) ($config['imagenes.max_ancho'] ?? $defaults['max_ancho']),
            'max_alto' => (int) ($config['imagenes.max_alto'] ?? $defaults['max_alto']),
            'calidad_png' => (int) ($config['imagenes.calidad_png'] ?? $defaults['calidad_png']),
        ];
    }

    private function toPngCompression(int $quality): int
    {
        $quality = max(0, min(100, $quality));
        $compression = (int) round((100 - $quality) / 10);
        return max(0, min(9, $compression));
    }
}
