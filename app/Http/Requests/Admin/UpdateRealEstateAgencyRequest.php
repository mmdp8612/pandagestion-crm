<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRealEstateAgencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('configuracion') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'razon_social' => ['required', 'string', 'max:150'],
            'telefonos' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'domicilio' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'partido' => ['nullable', 'string', 'max:100'],
            'localidad' => ['nullable', 'string', 'max:100'],
            'barrio' => ['nullable', 'string', 'max:100'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'web' => ['nullable', 'string', 'url:http,https', 'max:255'],
            'matricula' => ['nullable', 'string', 'max:150'],
            'logo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hab' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'razon_social.required' => 'La razón social es obligatoria.',
            'razon_social.max' => 'La razón social no puede superar los 150 caracteres.',
            'telefonos.max' => 'Los teléfonos no pueden superar los 255 caracteres.',
            'whatsapp.max' => 'El WhatsApp no puede superar los 50 caracteres.',
            'email.email' => 'Ingresá un correo electrónico válido.',
            'email.max' => 'El correo electrónico no puede superar los 255 caracteres.',
            'domicilio.max' => 'El domicilio no puede superar los 255 caracteres.',
            'codigo_postal.max' => 'El código postal no puede superar los 20 caracteres.',
            'provincia.max' => 'La provincia no puede superar los 100 caracteres.',
            'partido.max' => 'El partido no puede superar los 100 caracteres.',
            'localidad.max' => 'La localidad no puede superar los 100 caracteres.',
            'barrio.max' => 'El barrio no puede superar los 100 caracteres.',
            'latitud.numeric' => 'La latitud debe ser un número.',
            'latitud.between' => 'La latitud debe estar entre -90 y 90.',
            'longitud.numeric' => 'La longitud debe ser un número.',
            'longitud.between' => 'La longitud debe estar entre -180 y 180.',
            'web.url' => 'Ingresá una dirección web válida que comience con http:// o https://.',
            'web.max' => 'La dirección web no puede superar los 255 caracteres.',
            'matricula.max' => 'La matrícula no puede superar los 150 caracteres.',
            'logo.file' => 'El logo debe ser un archivo válido.',
            'logo.image' => 'El logo debe ser una imagen.',
            'logo.mimes' => 'El logo debe estar en formato JPG, PNG o WebP.',
            'logo.max' => 'El logo no puede superar los 2 MB.',
            'hab.required' => 'Indicá si la inmobiliaria está habilitada.',
            'hab.boolean' => 'El estado seleccionado no es válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $stringFields = [
            'razon_social',
            'telefonos',
            'whatsapp',
            'email',
            'domicilio',
            'codigo_postal',
            'provincia',
            'partido',
            'localidad',
            'barrio',
            'latitud',
            'longitud',
            'web',
            'matricula',
        ];

        $normalized = [];

        foreach ($stringFields as $field) {
            $value = trim((string) $this->input($field));
            $normalized[$field] = $value === '' ? null : $value;
        }

        if ($normalized['email'] !== null) {
            $normalized['email'] = mb_strtolower($normalized['email']);
        }

        $this->merge($normalized);
    }
}
