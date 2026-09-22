<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('roles') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(config('permission.table_names.roles'), 'name')
                    ->where('guard_name', 'web'),
            ],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => [
                'required',
                'string',
                'distinct',
                Rule::exists(config('permission.table_names.permissions'), 'name')
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
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.max' => 'El nombre del rol no puede superar los 255 caracteres.',
            'name.unique' => 'Ya existe un rol con ese nombre.',
            'permissions.required' => 'Seleccioná al menos un permiso.',
            'permissions.array' => 'La selección de permisos no es válida.',
            'permissions.min' => 'Seleccioná al menos un permiso.',
            'permissions.*.required' => 'La selección de permisos no es válida.',
            'permissions.*.distinct' => 'No se puede seleccionar un permiso más de una vez.',
            'permissions.*.exists' => 'Uno de los permisos seleccionados no es válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::lower(Str::squish((string) $this->input('name'))),
        ]);
    }
}
