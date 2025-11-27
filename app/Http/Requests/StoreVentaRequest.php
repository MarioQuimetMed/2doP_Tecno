<?php

namespace App\Http\Requests;

use App\Enums\TipoPago;
use App\Enums\MetodoPago;
use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
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
            // Viaje
            'viaje_id' => 'required|exists:viajes,id',
            'cantidad_personas' => 'required|integer|min:1|max:20',
            
            // Cliente
            'cliente_id' => 'required_without:nuevo_cliente|nullable|exists:users,id',
            'nuevo_cliente' => 'required_without:cliente_id|nullable|array',
            'nuevo_cliente.nombre' => 'required_with:nuevo_cliente|string|max:255',
            'nuevo_cliente.email' => 'required_with:nuevo_cliente|email|unique:users,email',
            'nuevo_cliente.telefono' => 'nullable|string|max:20',
            'nuevo_cliente.ci_nit' => 'required_with:nuevo_cliente|string|max:20',
            
            // Tipo de pago
            'tipo_pago' => 'required|in:' . implode(',', array_column(TipoPago::cases(), 'value')),
            
            // Opciones de crédito (solo si es crédito)
            'cantidad_cuotas' => 'required_if:tipo_pago,CREDITO|nullable|in:3,6,12',
            'dia_vencimiento' => 'nullable|integer|min:1|max:28',
            
            // Pago inicial (opcional)
            'pago_inicial' => 'nullable|array',
            'pago_inicial.monto' => 'required_with:pago_inicial|numeric|min:0',
            'pago_inicial.metodo' => 'required_with:pago_inicial|in:' . implode(',', array_column(MetodoPago::cases(), 'value')),
            'pago_inicial.referencia' => 'nullable|string|max:100',
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
            'viaje_id.required' => 'Debe seleccionar un viaje.',
            'viaje_id.exists' => 'El viaje seleccionado no existe.',
            'cantidad_personas.required' => 'Debe indicar la cantidad de personas.',
            'cantidad_personas.min' => 'Debe haber al menos 1 persona.',
            'cantidad_personas.max' => 'Máximo 20 personas por venta.',
            
            'cliente_id.required_without' => 'Debe seleccionar un cliente o crear uno nuevo.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            
            'nuevo_cliente.nombre.required_with' => 'El nombre del cliente es obligatorio.',
            'nuevo_cliente.email.required_with' => 'El email del cliente es obligatorio.',
            'nuevo_cliente.email.email' => 'El email debe ser válido.',
            'nuevo_cliente.email.unique' => 'Este email ya está registrado.',
            'nuevo_cliente.ci_nit.required_with' => 'El CI/NIT del cliente es obligatorio.',
            
            'tipo_pago.required' => 'Debe seleccionar el tipo de pago.',
            'tipo_pago.in' => 'El tipo de pago seleccionado no es válido.',
            
            'cantidad_cuotas.required_if' => 'Debe seleccionar la cantidad de cuotas para pago a crédito.',
            'cantidad_cuotas.in' => 'La cantidad de cuotas debe ser 3, 6 o 12.',
            
            'pago_inicial.monto.required_with' => 'El monto del pago es obligatorio.',
            'pago_inicial.monto.numeric' => 'El monto debe ser un número.',
            'pago_inicial.metodo.required_with' => 'El método de pago es obligatorio.',
            'pago_inicial.metodo.in' => 'El método de pago seleccionado no es válido.',
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
            'viaje_id' => 'viaje',
            'cantidad_personas' => 'cantidad de personas',
            'cliente_id' => 'cliente',
            'nuevo_cliente.nombre' => 'nombre del cliente',
            'nuevo_cliente.email' => 'email del cliente',
            'nuevo_cliente.telefono' => 'teléfono del cliente',
            'nuevo_cliente.ci_nit' => 'CI/NIT del cliente',
            'tipo_pago' => 'tipo de pago',
            'cantidad_cuotas' => 'cantidad de cuotas',
            'dia_vencimiento' => 'día de vencimiento',
            'pago_inicial.monto' => 'monto del pago inicial',
            'pago_inicial.metodo' => 'método de pago',
            'pago_inicial.referencia' => 'referencia del pago',
        ];
    }
}
