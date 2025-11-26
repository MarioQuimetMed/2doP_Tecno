<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDestinoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pais' => 'required|string|max:100',
            'ciudad' => 'required|string|max:100',
            'nombre_lugar' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:5000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pais.required' => 'El país es obligatorio.',
            'pais.max' => 'El país no puede tener más de :max caracteres.',
            'ciudad.required' => 'La ciudad es obligatoria.',
            'ciudad.max' => 'La ciudad no puede tener más de :max caracteres.',
            'nombre_lugar.required' => 'El nombre del lugar es obligatorio.',
            'nombre_lugar.max' => 'El nombre del lugar no puede tener más de :max caracteres.',
            'descripcion.max' => 'La descripción no puede tener más de :max caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'pais' => 'país',
            'ciudad' => 'ciudad',
            'nombre_lugar' => 'nombre del lugar',
            'descripcion' => 'descripción',
        ];
    }
}
