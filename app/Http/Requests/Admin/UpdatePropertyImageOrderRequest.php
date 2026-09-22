<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyImageOrderRequest extends FormRequest
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
            'imagenes' => ['required', 'array', 'min:1'],
            'imagenes.*' => ['required', 'integer', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'imagenes.required' => 'El orden de las imágenes es obligatorio.',
            'imagenes.array' => 'El orden de las imágenes no es válido.',
            'imagenes.min' => 'El orden debe incluir al menos una imagen.',
            'imagenes.*.required' => 'Una de las posiciones no contiene una imagen válida.',
            'imagenes.*.integer' => 'Una de las imágenes seleccionadas no es válida.',
            'imagenes.*.distinct' => 'Una imagen no puede aparecer más de una vez en el orden.',
        ];
    }
}
