<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class AdminUpdateUserPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $authenticatedUser = $this->user();
        $targetUser = $this->route('user');

        return ($authenticatedUser?->can('usuarios') ?? false)
            && $targetUser instanceof User
            && ! $authenticatedUser->is($targetUser);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $targetUser = $this->route('user');

                if (
                    ! $validator->errors()->has('password')
                    && $targetUser instanceof User
                    && Hash::check((string) $this->input('password'), $targetUser->password)
                ) {
                    $validator->errors()->add('password', 'La nueva contraseña debe ser diferente de la actual.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.mixed' => 'La nueva contraseña debe incluir mayúsculas y minúsculas.',
            'password.numbers' => 'La nueva contraseña debe incluir al menos un número.',
        ];
    }
}
