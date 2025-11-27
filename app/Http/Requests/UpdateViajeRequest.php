<?php

namespace App\Http\Requests;

use App\Enums\EstadoViaje;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateViajeRequest extends FormRequest
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
            'fecha_salida' => 'required|date',
            'cupos_totales' => 'required|integer|min:1|max:100',
            'estado_viaje' => ['required', new Enum(EstadoViaje::class)],
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
            'cupos_totales.required' => 'El número de cupos es obligatorio.',
            'cupos_totales.integer' => 'El número de cupos debe ser un número entero.',
            'cupos_totales.min' => 'Debe haber al menos 1 cupo disponible.',
            'cupos_totales.max' => 'El máximo de cupos es 100.',
            'estado_viaje.required' => 'El estado del viaje es obligatorio.',
            'estado_viaje.Illuminate\Validation\Rules\Enum' => 'El estado seleccionado no es válido.',
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
            'estado_viaje' => 'estado del viaje',
        ];
    }
}
