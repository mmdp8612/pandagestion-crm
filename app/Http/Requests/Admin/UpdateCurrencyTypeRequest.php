<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateCurrencyTypeRequest extends FormRequest
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
            'descripcion' => Str::squish((string) $this->input('descripcion')),
            'simbolo' => filled($this->input('simbolo'))
                ? Str::squish((string) $this->input('simbolo'))
                : null,
        ]);
    }
}
