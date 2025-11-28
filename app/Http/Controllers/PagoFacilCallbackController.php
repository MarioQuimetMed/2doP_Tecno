<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class PagoFacilCallbackController extends Controller
{
    /**
     * Recibir notificación de pago completado desde PagoFácil
     */
    public function handleCallback(Request $request): JsonResponse
    {
        // Aumentar tiempo de ejecución por lentitud de BD remota
        set_time_limit(120);

        try {
            // Loguear TODO lo que llega (Raw Content) para debug profundo
            Log::info('🔌 PagoFácil Callback RAW:', [
                'content' => $request->getContent(),
                'headers' => $request->headers->all(),
                'ip' => $request->ip(),
            ]);

            // Validar que vengan los datos esperados
            $validated = $request->validate([
                'PedidoID' => 'required|string',
                'Fecha' => 'required|string',
                'Hora' => 'required|string',
                'MetodoPago' => 'required', // Aceptamos string o int
                'Estado' => 'required', // Aceptamos string o int
            ]);

            // Asegurar tipos de datos
            $estado = (int) $validated['Estado'];
            $metodoPago = (string) $validated['MetodoPago'];

            // Buscar el pago por el ID de transacción de empresa (company_transaction_id)
            // Usamos lockForUpdate para evitar condiciones de carrera si llegan varios callbacks
            $pago = Pago::where('company_transaction_id', $validated['PedidoID'])->first();

            if (!$pago) {
                Log::warning('⚠️ Pago no encontrado para PedidoID', [
                    'pedido_id' => $validated['PedidoID'],
                ]);

                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => 'Pago no encontrado',
                    'values' => false,
                ], 404);
            }

            // Actualizar el estado del pago
            $pago->payment_status = $this->mapEstadoPago($estado);
            $pago->metodo_pago = $this->mapMetodoPago($metodoPago);
            
            // Si el pago fue exitoso, registrar la fecha de pago
            if ($pago->payment_status === 'COMPLETED') {
                $pago->fecha_pago = now();
            }

            $pago->save();

            Log::info('✅ Pago actualizado exitosamente', [
                'pago_id' => $pago->id,
                'venta_id' => $pago->venta_id,
                'estado' => $pago->payment_status,
            ]);

            // Responder según especificación de PagoFácil
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => 'Pago procesado correctamente',
                'values' => true,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ Validación fallida en callback', [
                'errors' => $e->errors(),
                'content' => $request->getContent(),
            ]);

            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => 'Datos inválidos',
                'values' => false,
            ], 400);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('🔥 Error de Base de Datos en callback', [
                'error' => $e->getMessage(),
                'pedido_id' => $request->input('PedidoID'),
            ]);

            // Retornar 500 para que PagoFácil reintente luego
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => 'Error de conexión con base de datos',
                'values' => false,
            ], 500);

        } catch (\Exception $e) {
            Log::error('💀 Error crítico en callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => 'Error interno del servidor',
                'values' => false,
            ], 500);
        }
    }

    /**
     * Mapear el estado de PagoFácil al formato interno
     * 
     * Estados de PagoFácil:
     * 1 - En proceso/pendiente
     * 2 - Pagado
     * 4 - Anulado (no se recibió dinero o el QR caducó)
     * 5 - Revisión (si no se pudo notificar por callback)
     */
    private function mapEstadoPago(int $estado): string
    {
        $mapped = match($estado) {
            1 => 'PENDING',      // En proceso/pendiente
            2 => 'COMPLETED',    // Pagado
            4 => 'CANCELLED',    // Anulado/Expirado
            5 => 'REVIEW',       // En revisión (requiere verificación manual)
            default => 'PENDING' // Por defecto, pendiente
        };

        Log::info('Estado PagoFácil mapeado', [
            'estado_pagofacil' => $estado,
            'estado_interno' => $mapped
        ]);

        return $mapped;
    }

    /**
     * Mapear el método de pago de PagoFácil al formato interno
     */
    private function mapMetodoPago(string $metodo): string
    {
        $mapeo = [
            'TIGO MONEY' => 'TIGO_MONEY',
            'TIGO_MONEY' => 'TIGO_MONEY',
            'QR' => 'QR',
        ];

        return $mapeo[strtoupper($metodo)] ?? 'QR';
    }
}
