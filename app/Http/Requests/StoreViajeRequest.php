<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreViajeRequest extends FormRequest
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
            'plan_viaje_id' => 'required|exists:plan_viajes,id',
            'fecha_salida' => 'required|date|after_or_equal:today',
            'cupos_totales' => 'required|integer|min:1|max:100',
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
            'plan_viaje_id.required' => 'Debe seleccionar un plan de viaje.',
            'plan_viaje_id.exists' => 'El plan de viaje seleccionado no es válido.',
            'fecha_salida.required' => 'La fecha de salida es obligatoria.',
            'fecha_salida.date' => 'La fecha de salida debe ser una fecha válida.',
            'fecha_salida.after_or_equal' => 'La fecha de salida debe ser hoy o una fecha futura.',
            'cupos_totales.required' => 'El número de cupos es obligatorio.',
            'cupos_totales.integer' => 'El número de cupos debe ser un número entero.',
            'cupos_totales.min' => 'Debe haber al menos 1 cupo disponible.',
            'cupos_totales.max' => 'El máximo de cupos es 100.',
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
            'plan_viaje_id' => 'plan de viaje',
            'fecha_salida' => 'fecha de salida',
            'cupos_totales' => 'cupos totales',
        ];
    }
}
