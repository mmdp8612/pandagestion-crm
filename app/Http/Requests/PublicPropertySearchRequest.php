<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicPropertySearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ubicacion' => ['nullable', 'string', 'max:120'],
            'operacion' => ['nullable', 'array', 'max:20'],
            'operacion.*' => [
                'string',
                'distinct',
                'max:3',
                Rule::exists('tip_comercializacion', 'IdComercializacion')
                    ->where(fn ($query) => $query->where('Hab', true)),
            ],
            'tipologia' => ['nullable', 'array', 'max:50'],
            'tipologia.*' => [
                'string',
                'distinct',
                'max:4',
                Rule::exists('tip_tipologia', 'IdTipologia')
                    ->where(fn ($query) => $query->where('Hab', true)),
            ],
            'ambientes' => ['nullable', 'array', 'max:50'],
            'ambientes.*' => [
                'integer',
                'distinct',
                'between:1,65535',
                Rule::exists('bienesraices', 'Ambientes')
                    ->where(fn ($query) => $query->where('Hab', true)),
            ],
            'provincia' => $this->publishedTextFacetRules(),
            'provincia.*' => $this->publishedTextFacetValueRules('Provincia'),
            'partido' => $this->publishedTextFacetRules(),
            'partido.*' => $this->publishedTextFacetValueRules('Partido'),
            'localidad' => $this->publishedTextFacetRules(),
            'localidad.*' => $this->publishedTextFacetValueRules('Localidad'),
            'cochera' => ['nullable', 'array', 'max:30'],
            'cochera.*' => [
                'string',
                'distinct',
                'max:3',
                Rule::exists('tip_cochera', 'IdCochera')
                    ->where(fn ($query) => $query->where('Hab', true)),
            ],
            'antiguedad' => ['nullable', 'array', 'max:30'],
            'antiguedad.*' => [
                'string',
                'distinct',
                'max:3',
                Rule::exists('tip_antiguedad', 'IdAntiguedad')
                    ->where(fn ($query) => $query->where('Hab', true)),
            ],
            'orientacion' => ['nullable', 'array', 'max:20'],
            'orientacion.*' => [
                'string',
                'distinct',
                'max:2',
                Rule::exists('tip_orientacion', 'IdOrientacion')
                    ->where(fn ($query) => $query->where('Hab', true)),
            ],
            'vista' => ['nullable', 'array', 'max:20'],
            'vista.*' => [
                'string',
                'distinct',
                'max:7',
                Rule::exists('tip_vista', 'IdVista')
                    ->where(fn ($query) => $query->where('Hab', true)),
            ],
            'moneda_venta' => $this->currencyRules(),
            'moneda_venta.*' => $this->currencyValueRules(),
            'moneda_alquiler' => $this->currencyRules(),
            'moneda_alquiler.*' => $this->currencyValueRules(),
            'orden' => ['nullable', 'string', Rule::in([
                'recientes',
                'antiguas',
                'superficie_desc',
                'superficie_asc',
            ])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ubicacion.max' => 'La ubicación no puede superar los 120 caracteres.',
            '*.array' => 'Los filtros seleccionados no tienen un formato válido.',
            '*.*.distinct' => 'Un filtro no puede repetirse.',
            'operacion.*.exists' => 'Una de las operaciones seleccionadas no está disponible.',
            'tipologia.*.exists' => 'Una de las tipologías seleccionadas no está disponible.',
            'ambientes.*.exists' => 'Una de las cantidades de ambientes seleccionadas no está disponible.',
            'provincia.*.exists' => 'Una de las provincias seleccionadas no está disponible.',
            'partido.*.exists' => 'Uno de los partidos seleccionados no está disponible.',
            'localidad.*.exists' => 'Una de las localidades seleccionadas no está disponible.',
            'cochera.*.exists' => 'Una de las cocheras seleccionadas no está disponible.',
            'antiguedad.*.exists' => 'Una de las antigüedades seleccionadas no está disponible.',
            'orientacion.*.exists' => 'Una de las orientaciones seleccionadas no está disponible.',
            'vista.*.exists' => 'Una de las vistas seleccionadas no está disponible.',
            'moneda_venta.*.exists' => 'Una de las monedas de venta seleccionadas no está disponible.',
            'moneda_alquiler.*.exists' => 'Una de las monedas de alquiler seleccionadas no está disponible.',
            'orden.in' => 'El orden seleccionado no es válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $location = Str::squish((string) $this->query('ubicacion'));
        $sort = trim((string) $this->query('orden'));

        $this->merge([
            'ubicacion' => $location !== '' ? $location : null,
            'operacion' => $this->normalizedList('operacion', true),
            'tipologia' => $this->normalizedList('tipologia', true),
            'ambientes' => $this->normalizedList('ambientes'),
            'provincia' => $this->normalizedList('provincia', squish: true),
            'partido' => $this->normalizedList('partido', squish: true),
            'localidad' => $this->normalizedList('localidad', squish: true),
            'cochera' => $this->normalizedList('cochera', true),
            'antiguedad' => $this->normalizedList('antiguedad', true),
            'orientacion' => $this->normalizedList('orientacion', true),
            'vista' => $this->normalizedList('vista', true),
            'moneda_venta' => $this->normalizedList('moneda_venta'),
            'moneda_alquiler' => $this->normalizedList('moneda_alquiler'),
            'orden' => $sort !== '' ? $sort : null,
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    private function publishedTextFacetRules(): array
    {
        return ['nullable', 'array', 'max:50'];
    }

    /**
     * @return array<int, mixed>
     */
    private function publishedTextFacetValueRules(string $column): array
    {
        return [
            'string',
            'distinct',
            'max:100',
            Rule::exists('bienesraices', $column)
                ->where(fn ($query) => $query->where('Hab', true)),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function currencyRules(): array
    {
        return ['nullable', 'array', 'max:10'];
    }

    /**
     * @return array<int, mixed>
     */
    private function currencyValueRules(): array
    {
        return [
            'integer',
            'distinct',
            'between:0,32767',
            Rule::exists('tip_tipomoneda', 'idTipoMoneda')
                ->where(fn ($query) => $query->where('Hab', true)),
        ];
    }

    /**
     * @return array<int, string>|null
     */
    private function normalizedList(string $key, bool $uppercase = false, bool $squish = false): ?array
    {
        $values = collect(Arr::wrap($this->query($key)))
            ->filter(fn ($value) => is_scalar($value))
            ->map(function ($value) use ($uppercase, $squish): string {
                $normalized = trim((string) $value);

                if ($squish) {
                    $normalized = Str::squish($normalized);
                }

                return $uppercase ? Str::upper($normalized) : $normalized;
            })
            ->filter(fn (string $value) => $value !== '')
            ->unique()
            ->values()
            ->all();

        return $values !== [] ? $values : null;
    }
}
