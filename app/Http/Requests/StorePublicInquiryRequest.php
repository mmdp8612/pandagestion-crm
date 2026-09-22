<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StorePublicInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'required_without:telefono', 'email:rfc', 'max:190'],
            'telefono' => ['nullable', 'required_without:email', 'string', 'max:50'],
            'mensaje' => ['required', 'string', 'max:2000'],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'Ingresá tu nombre.',
            'nombre.max' => 'El nombre no puede superar los 120 caracteres.',
            'email.required_without' => 'Ingresá un correo electrónico o un teléfono.',
            'email.email' => 'Ingresá un correo electrónico válido.',
            'email.max' => 'El correo electrónico no puede superar los 190 caracteres.',
            'telefono.required_without' => 'Ingresá un teléfono o un correo electrónico.',
            'telefono.max' => 'El teléfono no puede superar los 50 caracteres.',
            'mensaje.required' => 'Escribí tu consulta.',
            'mensaje.max' => 'La consulta no puede superar los 2000 caracteres.',
            'website.max' => 'No se pudo enviar la consulta.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = Str::squish((string) $this->input('nombre'));
        $email = Str::lower(trim((string) $this->input('email')));
        $phone = Str::squish((string) $this->input('telefono'));
        $message = trim((string) $this->input('mensaje'));
        $website = trim((string) $this->input('website'));

        $this->merge([
            'nombre' => $name,
            'email' => $email !== '' ? $email : null,
            'telefono' => $phone !== '' ? $phone : null,
            'mensaje' => $message,
            'website' => $website !== '' ? $website : null,
        ]);
    }
}
