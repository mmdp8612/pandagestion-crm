<?php

namespace App\Http\Requests\Admin;

use App\Support\PropertyVideo;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

abstract class SavePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('bienesraices') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'codigo' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Z0-9]+$/',
                $this->uniqueCodeRule(),
            ],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'id_tipologia' => [
                'nullable',
                'string',
                'max:4',
                $this->availableCatalogValueRule('tip_tipologia', 'IdTipologia', 'IdTipologia'),
            ],
            'id_uso' => [
                'nullable',
                'string',
                'max:4',
                $this->availableCatalogValueRule('tip_uso', 'IdUso', 'IdUso'),
            ],
            'antiguedad' => [
                'nullable',
                'string',
                'max:3',
                $this->availableCatalogValueRule('tip_antiguedad', 'IdAntiguedad', 'Antiguedad'),
            ],
            'id_orientacion' => [
                'nullable',
                'string',
                'max:2',
                $this->availableCatalogValueRule('tip_orientacion', 'IdOrientacion', 'IdOrientacion'),
            ],
            'id_cochera' => [
                'nullable',
                'string',
                'max:3',
                $this->availableCatalogValueRule('tip_cochera', 'IdCochera', 'IdCochera'),
            ],
            'id_vista' => [
                'nullable',
                'string',
                'max:7',
                $this->availableCatalogValueRule('tip_vista', 'IdVista', 'IdVista'),
            ],
            'id_comercializacion' => [
                'nullable',
                'string',
                'max:3',
                $this->availableCatalogValueRule('tip_comercializacion', 'IdComercializacion', 'IdComercializacion'),
            ],
            'importe_venta' => [
                'nullable',
                'required_with:id_tipo_moneda_venta',
                'numeric',
                'decimal:0,2',
                'between:0,9999999999999.99',
            ],
            'id_tipo_moneda_venta' => [
                'nullable',
                'required_with:importe_venta',
                'integer',
                'between:0,32767',
                $this->availableCatalogValueRule('tip_tipomoneda', 'idTipoMoneda', 'idTipoMonedaVta'),
            ],
            'importe_alquiler' => [
                'nullable',
                'required_with:id_tipo_moneda_alquiler',
                'numeric',
                'decimal:0,2',
                'between:0,9999999999999.99',
            ],
            'id_tipo_moneda_alquiler' => [
                'nullable',
                'required_with:importe_alquiler',
                'integer',
                'between:0,32767',
                $this->availableCatalogValueRule('tip_tipomoneda', 'idTipoMoneda', 'idTipoMonedaAlq'),
            ],
            'sup_cubierta_propia' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,9999999999.99'],
            'sup_terreno' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,9999999999.99'],
            'frente' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,9999999999.99'],
            'fondo' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,9999999999.99'],
            'metros_fondo' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,9999999999.99'],
            'luminosidad' => ['nullable', 'string', 'max:30'],
            'plantas' => ['nullable', 'integer', 'between:0,65535'],
            'ambientes' => ['nullable', 'integer', 'between:0,65535'],
            'sanitarios' => ['nullable', 'integer', 'between:0,65535'],
            'suite' => ['nullable', 'integer', 'between:0,65535'],
            'dormitorios' => ['nullable', 'integer', 'between:0,65535'],
            'lineas_telefonicas' => ['nullable', 'integer', 'between:0,65535'],
            'calle' => ['required', 'string', 'max:120'],
            'numero' => ['nullable', 'string', 'max:20'],
            'piso' => ['nullable', 'string', 'max:20'],
            'torre' => ['nullable', 'string', 'max:50'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'partido' => ['nullable', 'string', 'max:100'],
            'localidad' => ['nullable', 'string', 'max:100'],
            'barrio' => ['nullable', 'string', 'max:100'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],
            'latitud' => ['nullable', 'required_with:longitud', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'required_with:latitud', 'numeric', 'between:-180,180'],
            'destacada' => ['required', 'boolean'],
            'tiene_foto' => ['prohibited'],
            'tiene_video' => ['prohibited'],
            'video_url' => [
                'nullable',
                'string',
                'max:500',
                static function (string $attribute, mixed $value, Closure $fail): void {
                    if (PropertyVideo::parse(is_string($value) ? $value : null) === null) {
                        $fail('Ingresá una URL válida de YouTube o Vimeo.');
                    }
                },
            ],
            'regenerar_slug' => ['sometimes', 'boolean'],
            'hab' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.max' => 'El código no puede superar los 30 caracteres.',
            'codigo.regex' => 'El código solo puede contener letras y números.',
            'codigo.unique' => 'Ya existe una propiedad con ese código.',
            'descripcion.max' => 'La descripción no puede superar los 5000 caracteres.',
            'id_tipologia.exists' => 'La tipología seleccionada no está disponible.',
            'id_uso.exists' => 'El uso seleccionado no está disponible.',
            'antiguedad.exists' => 'La antigüedad seleccionada no está disponible.',
            'id_orientacion.exists' => 'La orientación seleccionada no está disponible.',
            'id_cochera.exists' => 'La cochera seleccionada no está disponible.',
            'id_vista.exists' => 'La vista seleccionada no está disponible.',
            'id_comercializacion.exists' => 'La modalidad comercial seleccionada no está disponible.',
            'importe_venta.required_with' => 'Ingresá el importe de venta junto con su moneda.',
            'importe_venta.numeric' => 'El importe de venta debe ser un número.',
            'importe_venta.decimal' => 'El importe de venta puede tener hasta dos decimales.',
            'importe_venta.between' => 'El importe de venta debe estar entre 0 y 9.999.999.999.999,99.',
            'id_tipo_moneda_venta.required_with' => 'Seleccioná la moneda del importe de venta.',
            'id_tipo_moneda_venta.integer' => 'La moneda de venta seleccionada no es válida.',
            'id_tipo_moneda_venta.between' => 'La moneda de venta seleccionada no es válida.',
            'id_tipo_moneda_venta.exists' => 'La moneda de venta seleccionada no está disponible.',
            'importe_alquiler.required_with' => 'Ingresá el importe de alquiler junto con su moneda.',
            'importe_alquiler.numeric' => 'El importe de alquiler debe ser un número.',
            'importe_alquiler.decimal' => 'El importe de alquiler puede tener hasta dos decimales.',
            'importe_alquiler.between' => 'El importe de alquiler debe estar entre 0 y 9.999.999.999.999,99.',
            'id_tipo_moneda_alquiler.required_with' => 'Seleccioná la moneda del importe de alquiler.',
            'id_tipo_moneda_alquiler.integer' => 'La moneda de alquiler seleccionada no es válida.',
            'id_tipo_moneda_alquiler.between' => 'La moneda de alquiler seleccionada no es válida.',
            'id_tipo_moneda_alquiler.exists' => 'La moneda de alquiler seleccionada no está disponible.',
            'sup_cubierta_propia.numeric' => 'La superficie cubierta propia debe ser un número.',
            'sup_cubierta_propia.decimal' => 'La superficie cubierta propia puede tener hasta dos decimales.',
            'sup_cubierta_propia.between' => 'La superficie cubierta propia debe estar entre 0 y 9.999.999.999,99.',
            'sup_terreno.numeric' => 'La superficie del terreno debe ser un número.',
            'sup_terreno.decimal' => 'La superficie del terreno puede tener hasta dos decimales.',
            'sup_terreno.between' => 'La superficie del terreno debe estar entre 0 y 9.999.999.999,99.',
            'frente.numeric' => 'La superficie del frente debe ser un número.',
            'frente.decimal' => 'La superficie del frente puede tener hasta dos decimales.',
            'frente.between' => 'La superficie del frente debe estar entre 0 y 9.999.999.999,99.',
            'fondo.numeric' => 'La superficie del fondo debe ser un número.',
            'fondo.decimal' => 'La superficie del fondo puede tener hasta dos decimales.',
            'fondo.between' => 'La superficie del fondo debe estar entre 0 y 9.999.999.999,99.',
            'metros_fondo.numeric' => 'Los metros de fondo deben ser un número.',
            'metros_fondo.decimal' => 'Los metros de fondo pueden tener hasta dos decimales.',
            'metros_fondo.between' => 'Los metros de fondo deben estar entre 0 y 9.999.999.999,99.',
            'luminosidad.max' => 'La luminosidad no puede superar los 30 caracteres.',
            'plantas.integer' => 'La cantidad de plantas debe ser un número entero.',
            'plantas.between' => 'La cantidad de plantas debe estar entre 0 y 65.535.',
            'ambientes.integer' => 'La cantidad de ambientes debe ser un número entero.',
            'ambientes.between' => 'La cantidad de ambientes debe estar entre 0 y 65.535.',
            'sanitarios.integer' => 'La cantidad de sanitarios debe ser un número entero.',
            'sanitarios.between' => 'La cantidad de sanitarios debe estar entre 0 y 65.535.',
            'suite.integer' => 'La cantidad de dormitorios en suite debe ser un número entero.',
            'suite.between' => 'La cantidad de dormitorios en suite debe estar entre 0 y 65.535.',
            'dormitorios.integer' => 'La cantidad de dormitorios debe ser un número entero.',
            'dormitorios.between' => 'La cantidad de dormitorios debe estar entre 0 y 65.535.',
            'lineas_telefonicas.integer' => 'La cantidad de líneas telefónicas debe ser un número entero.',
            'lineas_telefonicas.between' => 'La cantidad de líneas telefónicas debe estar entre 0 y 65.535.',
            'calle.required' => 'La calle es obligatoria.',
            'calle.max' => 'La calle no puede superar los 120 caracteres.',
            'numero.max' => 'El número no puede superar los 20 caracteres.',
            'piso.max' => 'El piso no puede superar los 20 caracteres.',
            'torre.max' => 'La torre no puede superar los 50 caracteres.',
            'provincia.max' => 'La provincia no puede superar los 100 caracteres.',
            'partido.max' => 'El partido no puede superar los 100 caracteres.',
            'localidad.max' => 'La localidad no puede superar los 100 caracteres.',
            'barrio.max' => 'El barrio no puede superar los 100 caracteres.',
            'codigo_postal.max' => 'El código postal no puede superar los 20 caracteres.',
            'latitud.required_with' => 'Ingresá la latitud junto con la longitud.',
            'latitud.numeric' => 'La latitud debe ser un número.',
            'latitud.between' => 'La latitud debe estar entre -90 y 90.',
            'longitud.required_with' => 'Ingresá la longitud junto con la latitud.',
            'longitud.numeric' => 'La longitud debe ser un número.',
            'longitud.between' => 'La longitud debe estar entre -180 y 180.',
            'destacada.required' => 'Indicá si la propiedad es destacada.',
            'destacada.boolean' => 'El estado destacado seleccionado no es válido.',
            'tiene_foto.prohibited' => 'El indicador de fotos se actualiza automáticamente desde la galería.',
            'tiene_video.prohibited' => 'El indicador de video se actualiza automáticamente desde su URL.',
            'video_url.string' => 'La URL del video no es válida.',
            'video_url.max' => 'La URL del video no puede superar los 500 caracteres.',
            'regenerar_slug.boolean' => 'La opción para regenerar el slug no es válida.',
            'hab.required' => 'Indicá si la propiedad está habilitada.',
            'hab.boolean' => 'El estado seleccionado no es válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $nullableFields = [
            'descripcion',
            'id_tipologia',
            'id_uso',
            'antiguedad',
            'id_orientacion',
            'id_cochera',
            'id_vista',
            'id_comercializacion',
            'importe_venta',
            'id_tipo_moneda_venta',
            'importe_alquiler',
            'id_tipo_moneda_alquiler',
            'sup_cubierta_propia',
            'sup_terreno',
            'frente',
            'fondo',
            'metros_fondo',
            'luminosidad',
            'plantas',
            'ambientes',
            'sanitarios',
            'suite',
            'dormitorios',
            'lineas_telefonicas',
            'numero',
            'piso',
            'torre',
            'provincia',
            'partido',
            'localidad',
            'barrio',
            'codigo_postal',
            'latitud',
            'longitud',
            'video_url',
        ];

        $normalized = [
            'codigo' => Str::upper(trim((string) $this->input('codigo'))),
            'calle' => trim((string) $this->input('calle')),
        ];

        foreach ($nullableFields as $field) {
            $value = trim((string) $this->input($field));
            $normalized[$field] = $value === '' ? null : $value;
        }

        foreach (['id_tipologia', 'id_uso', 'antiguedad', 'id_orientacion', 'id_cochera', 'id_vista', 'id_comercializacion'] as $field) {
            if ($normalized[$field] !== null) {
                $normalized[$field] = Str::upper($normalized[$field]);
            }
        }

        if ($normalized['luminosidad'] !== null) {
            $normalized['luminosidad'] = Str::squish($normalized['luminosidad']);
        }

        if ($normalized['video_url'] !== null) {
            $video = PropertyVideo::parse($normalized['video_url']);

            if ($video !== null) {
                $normalized['video_url'] = $video['url'];
            }
        }

        $this->merge($normalized);
    }

    protected function currentCatalogValue(string $propertyColumn): string|int|null
    {
        return null;
    }

    private function availableCatalogValueRule(string $table, string $catalogColumn, string $propertyColumn): Exists
    {
        $currentValue = $this->currentCatalogValue($propertyColumn);

        return Rule::exists($table, $catalogColumn)
            ->where(function (Builder $query) use ($catalogColumn, $currentValue): void {
                $query->where(function (Builder $availableValues) use ($catalogColumn, $currentValue): void {
                    $availableValues->where('Hab', true);

                    if ($currentValue !== null) {
                        $availableValues->orWhere($catalogColumn, $currentValue);
                    }
                });
            });
    }

    abstract protected function uniqueCodeRule(): Unique;
}
