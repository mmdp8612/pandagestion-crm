<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PropertyIndexRequest extends FormRequest
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
            'buscar' => ['nullable', 'string', 'max:120'],
            'tipologia' => ['nullable', 'string', 'max:4', Rule::exists('tip_tipologia', 'IdTipologia')],
            'comercializacion' => ['nullable', 'string', 'max:3', Rule::exists('tip_comercializacion', 'IdComercializacion')],
            'estado' => ['nullable', Rule::in(['habilitadas', 'deshabilitadas'])],
            'destacada' => ['nullable', Rule::in(['1'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'buscar.max' => 'La búsqueda no puede superar los 120 caracteres.',
            'tipologia.exists' => 'La tipología seleccionada no es válida.',
            'comercializacion.exists' => 'La comercialización seleccionada no es válida.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'destacada.in' => 'El filtro de propiedades destacadas no es válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $search = Str::squish((string) $this->query('buscar'));
        $typology = Str::upper(trim((string) $this->query('tipologia')));
        $commercialization = Str::upper(trim((string) $this->query('comercializacion')));
        $status = trim((string) $this->query('estado'));
        $featured = $this->query('destacada');

        if (is_string($featured)) {
            $featured = trim($featured);
        }

        $this->merge([
            'buscar' => $search !== '' ? $search : null,
            'tipologia' => $typology !== '' ? $typology : null,
            'comercializacion' => $commercialization !== '' ? $commercialization : null,
            'estado' => $status !== '' ? $status : null,
            'destacada' => $featured !== '' ? $featured : null,
        ]);
    }
}
