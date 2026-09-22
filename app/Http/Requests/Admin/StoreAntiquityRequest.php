<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreAntiquityRequest extends FormRequest
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
                'max:3',
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('tip_antiguedad', 'IdAntiguedad'),
            ],
            'descripcion' => ['required', 'string', 'max:15'],
            'orden' => ['required', 'integer', 'min:0', 'max:2147483647'],
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
            'id.max' => 'El código no puede superar los 3 caracteres.',
            'id.regex' => 'El código solo puede contener letras y números.',
            'id.unique' => 'Ya existe una antigüedad con ese código.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede superar los 15 caracteres.',
            'orden.required' => 'El orden es obligatorio.',
            'orden.integer' => 'El orden debe ser un número entero.',
            'orden.min' => 'El orden no puede ser negativo.',
            'orden.max' => 'El orden ingresado es demasiado grande.',
            'hab.required' => 'Indicá si la antigüedad está habilitada.',
            'hab.boolean' => 'El estado seleccionado no es válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => Str::upper(trim((string) $this->input('id'))),
            'descripcion' => Str::squish((string) $this->input('descripcion')),
        ]);
    }
}
