<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateTypologyRequest extends FormRequest
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
            'descripcion' => Str::squish((string) $this->input('descripcion')),
            'tipo_general' => $generalType !== '' ? $generalType : null,
        ]);
    }
}
