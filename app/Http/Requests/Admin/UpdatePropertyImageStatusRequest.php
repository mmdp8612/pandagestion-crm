<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyImageStatusRequest extends FormRequest
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
            'hab' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hab.required' => 'El estado de la imagen es obligatorio.',
            'hab.boolean' => 'El estado de la imagen no es válido.',
        ];
    }
}
