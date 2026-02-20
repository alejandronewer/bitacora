<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvImportReglaRequest extends FormRequest
{
    private const TABLA_DESTINO = 'inv_elementos_redes';

    private const DESTINOS_POR_TIPO = [
        'nodo' => [
            'codigo',
            'nombre',
            'observaciones',
            'ne_id',
            'ne_dbid',
            'dn_externo',
            'native_name',
            'user_label',
            'nombre_producto',
            'tipo_equipo',
            'version_me',
            'direccion_red',
            'nombre_grupo',
        ],
        'enlace' => [
            'codigo',
            'nombre',
            'observaciones',
            'instancia_enlace_id',
            'motlink_label',
            'trail_id',
            'nodo_a_ne_id',
            'nodo_z_ne_id',
        ],
        'servicio' => [
            'codigo',
            'nombre',
            'observaciones',
            'instancia_servicio_id',
            'user_label',
            'cliente',
            'tipo_servicio',
            'ethvpn_id',
        ],
        'tunel' => [
            'codigo',
            'nombre',
            'observaciones',
            'instancia_tunel_id',
            'user_label',
            'cliente',
            'tipo_tunel',
            'ethvpn_id',
        ],
    ];

    protected function prepareForValidation(): void
    {
        $campos = $this->input('campos');
        if (is_array($campos)) {
            foreach ($campos as $idx => $campo) {
                if (! is_array($campo)) {
                    continue;
                }

                $campos[$idx]['campo_destino'] = $this->normalizeCampoDestino($campo['campo_destino'] ?? '');
            }
        }

        $this->merge([
            'tabla_destino' => self::TABLA_DESTINO,
            'campos' => $campos,
        ]);
    }

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }

    public function rules(): array
    {
        return [
            'red_id' => ['required', 'integer', 'exists:inv_redes,id'],
            'nombre' => [
                'required',
                'string',
                'max:120',
                Rule::unique('inv_import_reglas', 'nombre')->where(fn ($q) => $q->where('red_id', $this->input('red_id'))),
            ],
            'tipo_elemento' => ['required', Rule::in(['nodo', 'enlace', 'servicio', 'tunel'])],
            'tabla_destino' => ['required', 'string', Rule::in([self::TABLA_DESTINO])],
            'archivo_patron' => ['nullable', 'string', 'max:255'],
            'delimitador' => ['required', 'string', 'size:1'],
            'usa_comillas' => ['boolean'],
            'tiene_encabezado' => ['boolean'],
            'encoding' => ['nullable', 'string', 'max:40'],
            'activo' => ['boolean'],
            'campos' => [
                'nullable',
                'array',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_array($value) || $value === []) {
                        return;
                    }

                    $this->validateCampos($value, $this->boolean('tiene_encabezado'), $fail);
                },
            ],
            'campos.*.columna_fuente' => ['nullable', 'string', 'max:120'],
            'campos.*.campo_destino' => ['required_with:campos', 'string', 'max:120'],
            'campos.*.transformacion' => ['nullable', 'string', 'max:80'],
            'campos.*.por_defecto' => ['nullable', 'string', 'max:255'],
            'campos.*.es_clave_upsert' => ['boolean'],
            'campos.*.orden' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'campos.*.activo' => ['boolean'],
        ];
    }

    private function validateCampos(array $campos, bool $tieneEncabezado, \Closure $fail): void
    {
        $tipo = mb_strtolower(trim((string) $this->input('tipo_elemento')));
        $destinosPermitidos = $this->allowedDestinosForTipo($tipo);
        $hasCodigo = false;

        foreach ($campos as $idx => $campo) {
            $destino = $this->normalizeCampoDestino($campo['campo_destino'] ?? '');
            $fuente = trim((string) ($campo['columna_fuente'] ?? ''));
            $default = trim((string) ($campo['por_defecto'] ?? ''));
            $tieneValor = $fuente !== '' || $default !== '';

            if ($destino === '' || ! in_array($destino, $destinosPermitidos, true)) {
                $fail('En campos[' . ($idx + 1) . '].campo_destino el valor "' . $destino . '" no está permitido para el tipo "' . $tipo . '".');
                continue;
            }

            if (in_array($destino, ['codigo', 'code', 'id'], true) && $tieneValor) {
                $hasCodigo = true;
            }

            if (! $tieneEncabezado && $fuente !== '' && ! is_numeric($fuente)) {
                $fail('En campos[' . ($idx + 1) . '].columna_fuente debe usar índice numérico cuando "Tiene encabezado" está desactivado.');
            }
        }

        if (! $hasCodigo) {
            $fail('Debe existir un mapeo para "codigo" con columna fuente o valor por defecto.');
        }
    }

    private function allowedDestinosForTipo(?string $tipo): array
    {
        return self::DESTINOS_POR_TIPO[$tipo] ?? self::DESTINOS_POR_TIPO['nodo'];
    }

    private function normalizeCampoDestino(mixed $value): string
    {
        $raw = mb_strtolower(trim((string) $value));
        if ($raw === '') {
            return '';
        }

        return match ($raw) {
            'codigo', 'code', 'id' => 'codigo',
            'nombre', 'name' => 'nombre',
            'observaciones' => 'observaciones',
            'ne_id' => 'ne_id',
            'ne_dbid' => 'ne_dbid',
            'dn_externo', 'external_dn', 'dn', 'ne_m_dn' => 'dn_externo',
            'native_name', 'm_nativename' => 'native_name',
            'user_label', 'ne_m_userlabel' => 'user_label',
            'nombre_producto', 'm_productname', 'product_name' => 'nombre_producto',
            'tipo_equipo', 't_oranetype_name', 'm_netype', 'me_type' => 'tipo_equipo',
            'version_me', 'm_version' => 'version_me',
            'direccion_red', 'm_networkaddress', 'network_address' => 'direccion_red',
            'nombre_grupo', 'group_name', 'm_userlabel' => 'nombre_grupo',
            'instancia_enlace_id', 'link_instance_id', 'motlinkinstanceid' => 'instancia_enlace_id',
            'motlink_label', 'motlinklabel' => 'motlink_label',
            'trail_id', 'trail_subyacente_id', 'underlyingtrailid_id' => 'trail_id',
            'nodo_a_ne_id', 'nodo_a_me_id' => 'nodo_a_ne_id',
            'nodo_z_ne_id', 'nodo_b_me_id' => 'nodo_z_ne_id',
            'instancia_servicio_id', 'service_instance_id' => 'instancia_servicio_id',
            'cliente', 'customer', 'm_customer' => 'cliente',
            'tipo_servicio', 'service_type', 'm_servicetype', 'servicetype' => 'tipo_servicio',
            'ethvpn_id', 'm_ethvpnid' => 'ethvpn_id',
            'instancia_tunel_id', 'tunnel_instance_id' => 'instancia_tunel_id',
            'tipo_tunel', 'tunnel_type', 'm_tunneltype' => 'tipo_tunel',
            default => $raw,
        };
    }
}
