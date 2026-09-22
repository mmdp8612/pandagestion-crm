<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('usuarios') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'role_id' => [
                'nullable',
                'integer',
                Rule::exists(config('permission.table_names.roles'), 'id')
                    ->where('guard_name', 'web'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'search.max' => 'La búsqueda no puede superar los 255 caracteres.',
            'role_id.integer' => 'El rol seleccionado no es válido.',
            'role_id.exists' => 'El rol seleccionado no es válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $search = Str::squish((string) $this->query('search'));

        $this->merge([
            'search' => $search !== '' ? $search : null,
            'role_id' => $this->query('role_id') ?: null,
        ]);
    }
}
