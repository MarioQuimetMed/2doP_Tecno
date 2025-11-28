<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cuota;
use App\Models\Pago;
use App\Models\Venta;
use App\Enums\EstadoCuota;
use App\Enums\MetodoPago;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ClienteController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Resumen para el dashboard
        $ventasActivas = Venta::where('cliente_id', $user->id)
            ->where('estado_pago', '!=', 'Anulado')
            ->count();
            
        $cuotasPendientes = Cuota::whereHas('planPago.venta', function($q) use ($user) {
            $q->where('cliente_id', $user->id);
        })->where('estado_cuota', EstadoCuota::PENDIENTE->value)->count();

        $proximoVencimiento = Cuota::whereHas('planPago.venta', function($q) use ($user) {
            $q->where('cliente_id', $user->id);
        })->where('estado_cuota', EstadoCuota::PENDIENTE->value)
          ->orderBy('fecha_vencimiento')
          ->first();

        return Inertia::render('Cliente/Inicio', [
            'stats' => [
                'ventas_activas' => $ventasActivas,
                'cuotas_pendientes' => $cuotasPendientes,
                'proximo_vencimiento' => $proximoVencimiento ? $proximoVencimiento->fecha_vencimiento->format('d/m/Y') : 'No hay',
            ]
        ]);
    }

    public function misCuotas()
    {
        $user = auth()->user();

        $cuotas = Cuota::with(['planPago.venta.viaje.planViaje.destino'])
            ->whereHas('planPago.venta', function($q) use ($user) {
                $q->where('cliente_id', $user->id);
            })
            ->orderBy('estado_cuota', 'asc') // Pendientes primero
            ->orderBy('fecha_vencimiento', 'asc')
            ->get()
            ->map(function ($cuota) {
                return [
                    'id' => $cuota->id,
                    'numero_cuota' => $cuota->numero_cuota,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento->format('d/m/Y'),
                    'monto_total' => $cuota->monto_total_cuota,
                    'saldo_pendiente' => $cuota->saldoPendiente(),
                    'estado' => $cuota->estado_cuota,
                    'destino' => $cuota->planPago->venta->viaje->planViaje->destino->nombre_lugar,
                    'viaje_fecha' => $cuota->planPago->venta->viaje->fecha_salida->format('d/m/Y'),
                    'puede_pagar' => $cuota->estado_cuota !== EstadoCuota::PAGADO,
                ];
            });

        return Inertia::render('Cliente/Cuotas/Index', [
            'cuotas' => $cuotas
        ]);
    }

    public function generarQrCuota(Request $request, Cuota $cuota)
    {
        // Validar que la cuota pertenezca al cliente
        if ($cuota->planPago->venta->cliente_id !== auth()->id()) {
            abort(403, 'No tienes permiso para pagar esta cuota.');
        }

        if ($cuota->estado_cuota === EstadoCuota::PAGADO) {
            return response()->json(['error' => 'Esta cuota ya está pagada.'], 400);
        }

        $monto = $cuota->saldoPendiente();
        
        try {
            $pagoFacilService = app(PagoFacilService::class);
            
            // Generar ID único de transacción
            $companyTransactionId = 'grupo15sa_CUOTA-' . $cuota->id . '-' . Str::random(8);

            // Crear registro de pago pendiente
            $pago = Pago::create([
                'venta_id' => $cuota->planPago->venta_id,
                'cuota_id' => $cuota->id,
                'fecha_pago' => null,
                'monto_pagado' => $monto,
                'metodo_pago' => MetodoPago::QR->value,
                'company_transaction_id' => $companyTransactionId,
                'payment_status' => 'PENDING',
            ]);

            // Preparar parámetros y generar QR
            $venta = $cuota->planPago->venta;
            $params = $pagoFacilService->prepareQRParams($venta, $monto, $companyTransactionId);
            
            $qrData = $pagoFacilService->generateQR($params);

            // Actualizar pago con datos del QR
            $pago->update([
                'pagofacil_transaction_id' => $qrData['transactionId'],
                'qr_base64' => $qrData['qrBase64'] ?? null,
                'checkout_url' => $qrData['checkoutUrl'] ?? null,
                'qr_expiration_date' => $qrData['expirationDate'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'pago_id' => $pago->id,
                'qr_image' => $qrData['qrBase64'],
                'monto' => $monto,
                'expiration' => $qrData['expirationDate']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar QR: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkPagoStatus(Pago $pago)
    {
        // Validar propiedad
        if ($pago->venta->cliente_id !== auth()->id()) {
            abort(403);
        }

        // Si ya está pagado en BD local
        if ($pago->fecha_pago && $pago->payment_status === 'COMPLETED') {
            return response()->json(['status' => 'COMPLETED']);
        }
            
        // Retornamos el estado actual de la BD (el polling en frontend llamará repetidamente)
        // El callback de PagoFacil se encargará de actualizar el estado a COMPLETED
        return response()->json(['status' => $pago->payment_status]);
    }
}
