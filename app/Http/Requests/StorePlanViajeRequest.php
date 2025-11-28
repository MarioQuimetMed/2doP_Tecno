<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanViajeRequest extends FormRequest
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
            'destino_id' => 'required|exists:destinos,id',
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:5000',
            'duracion_dias' => 'required|integer|min:1|max:30',
            'precio_base' => 'required|numeric|min:0|max:99999.99',
            
            // Validación de actividades (array opcional)
            'actividades' => 'nullable|array',
            'actividades.*.dia_numero' => 'required_with:actividades|integer|min:1',
            'actividades.*.descripcion_actividad' => 'required_with:actividades|string|max:500',
            'actividades.*.hora_inicio' => 'nullable|date_format:H:i',
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
            'destino_id.required' => 'Debe seleccionar un destino.',
            'destino_id.exists' => 'El destino seleccionado no es válido.',
            'nombre.required' => 'El nombre del plan es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de :max caracteres.',
            'descripcion.max' => 'La descripción no puede tener más de :max caracteres.',
            'duracion_dias.required' => 'La duración en días es obligatoria.',
            'duracion_dias.integer' => 'La duración debe ser un número entero.',
            'duracion_dias.min' => 'La duración mínima es de 1 día.',
            'duracion_dias.max' => 'La duración máxima es de 30 días.',
            'precio_base.required' => 'El precio base es obligatorio.',
            'precio_base.numeric' => 'El precio debe ser un número válido.',
            'precio_base.min' => 'El precio no puede ser negativo.',
            'precio_base.max' => 'El precio máximo es de $99,999.99.',
            
            'actividades.*.dia_numero.required_with' => 'El número de día es obligatorio para cada actividad.',
            'actividades.*.dia_numero.integer' => 'El número de día debe ser un entero.',
            'actividades.*.dia_numero.min' => 'El número de día debe ser al menos 1.',
            'actividades.*.descripcion_actividad.required_with' => 'La descripción de la actividad es obligatoria.',
            'actividades.*.descripcion_actividad.max' => 'La descripción de la actividad no puede tener más de :max caracteres.',
            'actividades.*.hora_inicio.date_format' => 'La hora de inicio debe tener el formato HH:MM.',
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
            'destino_id' => 'destino',
            'nombre' => 'nombre del plan',
            'descripcion' => 'descripción',
            'duracion_dias' => 'duración en días',
            'precio_base' => 'precio base',
            'actividades.*.dia_numero' => 'día',
            'actividades.*.descripcion_actividad' => 'descripción de actividad',
            'actividades.*.hora_inicio' => 'hora de inicio',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Filtrar actividades vacías
        if ($this->has('actividades')) {
            $actividades = collect($this->actividades)
                ->filter(function ($actividad) {
                    return !empty($actividad['descripcion_actividad']);
                })
                ->values()
                ->toArray();
            
            $this->merge(['actividades' => $actividades]);
        }
    }
}
