<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $targetUser = $this->route('user');
        $authenticatedUser = $this->user();

        if (! $authenticatedUser?->can('usuarios') || ! $targetUser instanceof User) {
            return false;
        }

        return ! ($authenticatedUser->is($targetUser) && ! $this->boolean('is_active'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'is_active.required' => 'El estado del usuario es obligatorio.',
            'is_active.boolean' => 'El estado del usuario no es válido.',
        ];
    }
}
