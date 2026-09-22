<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreTypologyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalogo') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'string',
                'max:4',
                'regex:/^[A-Z0-9-]+$/',
                Rule::unique('tip_tipologia', 'IdTipologia'),
            ],
            'descripcion' => ['required', 'string', 'max:20'],
            'tipo_general' => ['nullable', 'string', 'max:15'],
            'orden_tipo_general' => ['required', 'integer', 'min:0', 'max:32767'],
            'hab' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'El código es obligatorio.',
            'id.max' => 'El código no puede superar los 4 caracteres.',
            'id.regex' => 'El código solo puede contener letras, números y guiones.',
            'id.unique' => 'Ya existe una tipología con ese código.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede superar los 20 caracteres.',
            'tipo_general.max' => 'El tipo general no puede superar los 15 caracteres.',
            'orden_tipo_general.required' => 'El orden del tipo general es obligatorio.',
            'orden_tipo_general.integer' => 'El orden del tipo general debe ser un número entero.',
            'orden_tipo_general.min' => 'El orden del tipo general no puede ser negativo.',
            'orden_tipo_general.max' => 'El orden del tipo general no puede superar 32767.',
            'hab.required' => 'Indicá si la tipología está habilitada.',
            'hab.boolean' => 'El estado seleccionado no es válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $generalType = Str::squish((string) $this->input('tipo_general'));

        $this->merge([
            'id' => Str::upper(trim((string) $this->input('id'))),
            'descripcion' => Str::squish((string) $this->input('descripcion')),
            'tipo_general' => $generalType !== '' ? $generalType : null,
        ]);
    }
}
