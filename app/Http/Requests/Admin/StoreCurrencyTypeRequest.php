<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCurrencyTypeRequest extends FormRequest
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
                'integer',
                'min:0',
                'max:32767',
                Rule::unique('tip_tipomoneda', 'idTipoMoneda'),
            ],
            'descripcion' => ['required', 'string', 'max:10'],
            'simbolo' => ['nullable', 'string', 'max:3'],
            'hab' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'El identificador es obligatorio.',
            'id.integer' => 'El identificador debe ser un número entero.',
            'id.min' => 'El identificador no puede ser negativo.',
            'id.max' => 'El identificador no puede superar 32767.',
            'id.unique' => 'Ya existe un tipo de moneda con ese identificador.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede superar los 10 caracteres.',
            'simbolo.max' => 'El símbolo no puede superar los 3 caracteres.',
            'hab.required' => 'Indicá si el tipo de moneda está habilitado.',
            'hab.boolean' => 'El estado seleccionado no es válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => trim((string) $this->input('id')),
            'descripcion' => Str::squish((string) $this->input('descripcion')),
            'simbolo' => filled($this->input('simbolo'))
                ? Str::squish((string) $this->input('simbolo'))
                : null,
        ]);
    }
}
