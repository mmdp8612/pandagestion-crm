<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyImagesRequest extends FormRequest
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
            'imagenes' => ['required', 'array', 'min:1', 'max:10'],
            'imagenes.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'imagenes.required' => 'Seleccioná al menos una imagen.',
            'imagenes.array' => 'La selección de imágenes no es válida.',
            'imagenes.min' => 'Seleccioná al menos una imagen.',
            'imagenes.max' => 'Podés cargar hasta 10 imágenes por vez.',
            'imagenes.*.required' => 'Una de las imágenes seleccionadas no es válida.',
            'imagenes.*.image' => 'Cada archivo debe ser una imagen válida.',
            'imagenes.*.mimes' => 'Las imágenes deben estar en formato JPG, PNG o WebP.',
            'imagenes.*.max' => 'Cada imagen puede pesar hasta 5 MB.',
        ];
    }
}
