<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreOrientationRequest extends FormRequest
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
                'max:2',
                'regex:/^[A-Z0-9-]+$/',
                Rule::unique('tip_orientacion', 'IdOrientacion'),
            ],
            'descripcion' => ['required', 'string', 'max:15'],
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
            'id.max' => 'El código no puede superar los 2 caracteres.',
            'id.regex' => 'El código solo puede contener letras, números y guiones.',
            'id.unique' => 'Ya existe una orientación con ese código.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede superar los 15 caracteres.',
            'hab.required' => 'Indicá si la orientación está habilitada.',
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
