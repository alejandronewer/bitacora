<?php

namespace App\Http\Requests\Api;

use App\Models\ConfiguracionSistema;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdjuntoArchivoRequest extends FormRequest
{
    private ?array $allowedExtensions = null;
    private ?int $maxFileMb = null;

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['operador', 'administrador']);
    }

    public function rules(): array
    {
        $extensions = $this->allowedExtensions();
        $maxKb = $this->maxFileMb() * 1024;

        $fileRules = ['required', 'file', 'max:'.$maxKb];
        if (! empty($extensions)) {
            $fileRules[] = 'mimes:'.implode(',', $extensions);
        }

        return [
            'file' => $fileRules,
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (empty($this->allowedExtensions())) {
                $validator->errors()->add('file', 'No hay tipos de archivos habilitados para subir.');
            }
        });
    }

    private function allowedExtensions(): array
    {
        if ($this->allowedExtensions !== null) {
            return $this->allowedExtensions;
        }

        $map = [
            'pdf' => 'archivos.permitir_pdf',
            'doc' => 'archivos.permitir_doc',
            'docx' => 'archivos.permitir_docx',
            'xls' => 'archivos.permitir_xls',
            'xlsx' => 'archivos.permitir_xlsx',
        ];

        $config = ConfiguracionSistema::whereIn('clave', array_values($map))->pluck('valor', 'clave');

        $allowed = [];
        foreach ($map as $ext => $key) {
            $raw = $config[$key] ?? '1';
            $enabled = in_array(strtolower((string) $raw), ['1', 'true', 'yes'], true);
            if ($enabled) {
                $allowed[] = $ext;
            }
        }

        $this->allowedExtensions = $allowed;
        return $allowed;
    }

    private function maxFileMb(): int
    {
        if ($this->maxFileMb !== null) {
            return $this->maxFileMb;
        }

        $value = ConfiguracionSistema::where('clave', 'archivos.max_mb')->value('valor');
        $mb = (int) ($value ?? 5);
        if ($mb <= 0) {
            $mb = 5;
        }
        $mb = min($mb, 10);

        $this->maxFileMb = $mb;
        return $mb;
    }
}
