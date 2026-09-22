<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyStatusRequest extends FormRequest
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
            'hab.required' => 'Indicá el nuevo estado de la propiedad.',
            'hab.boolean' => 'El estado seleccionado no es válido.',
        ];
    }
}
