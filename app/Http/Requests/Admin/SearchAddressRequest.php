<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SearchAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && ($user->can('configuracion') || $user->can('bienesraices'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:5', 'max:200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'query.required' => 'Ingresá una dirección para buscar.',
            'query.min' => 'Ingresá al menos 5 caracteres para buscar.',
            'query.max' => 'La búsqueda no puede superar los 200 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $query = preg_replace('/\s+/u', ' ', trim((string) $this->input('query')));

        $this->merge([
            'query' => $query ?? '',
        ]);
    }
}
