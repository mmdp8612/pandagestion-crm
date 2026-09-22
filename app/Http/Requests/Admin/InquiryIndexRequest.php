<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InquiryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('consultas') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', Rule::in(['nueva', 'en_proceso', 'respondida', 'descartada'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'buscar.max' => 'La búsqueda no puede superar los 120 caracteres.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $search = Str::squish((string) $this->query('buscar'));
        $status = trim((string) $this->query('estado'));

        $this->merge([
            'buscar' => $search !== '' ? $search : null,
            'estado' => $status !== '' ? $status : null,
        ]);
    }
}
