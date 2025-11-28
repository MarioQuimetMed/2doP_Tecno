<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Venta;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PagoQRController extends Controller
{
    protected PagoFacilService $pagoFacilService;

    public function __construct(PagoFacilService $pagoFacilService)
    {
        $this->pagoFacilService = $pagoFacilService;
    }

    /**
     * Generar QR para un pago
     */
    public function generarQR(Request $request, Venta $venta)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01|max:' . $venta->saldoPendiente(),
        ]);

        try {
            $monto = $request->monto;
            
            // Generar ID único de transacción de empresa
            $companyTransactionId = 'PAGO-' . $venta->id . '-' . Str::random(8);

            // Crear registro de pago pendiente
            $pago = Pago::create([
                'venta_id' => $venta->id,
                'cuota_id' => $request->cuota_id ?? null,
                'fecha_pago' => null, // Se llenará cuando se confirme el pago
                'monto_pagado' => $monto,
                'metodo_pago' => 'QR',
                'company_transaction_id' => $companyTransactionId,
                'payment_status' => 'PENDING',
            ]);

            // Preparar parámetros para PagoFácil
            $params = $this->pagoFacilService->prepareQRParams($venta, $monto, $companyTransactionId);

            // Generar QR
            $qrData = $this->pagoFacilService->generateQR($params);

            // Actualizar el pago con los datos del QR
            $pago->update([
                'pagofacil_transaction_id' => $qrData['transactionId'],
                'qr_base64' => $qrData['qrBase64'] ?? null,
                'checkout_url' => $qrData['checkoutUrl'] ?? null,
                'deep_link' => $qrData['deepLink'] ?? null,
                'qr_content_url' => $qrData['qrContentUrl'] ?? null,
                'universal_url' => $qrData['universalUrl'] ?? null,
                'qr_expiration_date' => $qrData['expirationDate'] ?? null,
            ]);

            // Redirigir a la página para mostrar el QR
            return redirect()->route('pagos.mostrar-qr', $pago->id)
                ->with('success', 'QR generado exitosamente');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al generar QR: ' . $e->getMessage()]);
        }
    }

    /**
     * Consultar el estado de un pago (localmente)
     */
    public function consultarEstado(Pago $pago)
    {
        // La consulta de estado se realiza automáticamente vía Callback (Webhook)
        // Aquí solo verificamos el estado local actual
        
        $estado = $pago->payment_status;
        
        if ($estado === 'COMPLETED') {
            return back()->with('success', 'El pago ya ha sido completado y verificado.');
        }

        return back()->with('info', 'Estado actual: ' . $estado . '. La actualización es automática cuando se recibe el pago.');
    }

    /**
     * Mostrar página de pago con QR
     */
    public function mostrarQR(Pago $pago)
    {
        if (!$pago->qr_base64 && !$pago->checkout_url) {
            return redirect()->route('ventas.show', $pago->venta_id)
                ->withErrors(['error' => 'No hay QR disponible para este pago']);
        }

        return Inertia::render('Admin/Pagos/MostrarQR', [
            'pago' => $pago->load(['venta.cliente', 'venta.viaje.planViaje']),
        ]);
    }

    /**
     * Mapear estado de pago de PagoFácil
     */
    private function mapPaymentStatus($statusId): string
    {
        // Mapeo aproximado - ajusta según documentación real
        $mapeo = [
            1 => 'PENDING',
            2 => 'COMPLETED',
            4 => 'CANCELLED',
            5 => 'REVIEW',
        ];

        return $mapeo[$statusId] ?? 'PENDING';
    }
}
