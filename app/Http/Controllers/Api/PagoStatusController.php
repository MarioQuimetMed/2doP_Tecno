<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use Illuminate\Http\JsonResponse;

class PagoStatusController extends Controller
{
    /**
     * Consultar estado de un pago (usado para polling desde frontend)
     */
    public function checkStatus(Pago $pago): JsonResponse
    {
        // Cargar relación de venta si se necesita
        $pago->load('venta');

        return response()->json([
            'id' => $pago->id,
            'payment_status' => $pago->payment_status,
            'fecha_pago' => $pago->fecha_pago?->format('Y-m-d H:i:s'),
            'qr_expiration_date' => $pago->qr_expiration_date?->format('Y-m-d H:i:s'),
            'is_paid' => $pago->payment_status === 'PAID',
            'is_pending' => $pago->payment_status === 'PENDING',
            'is_expired' => $pago->payment_status === 'EXPIRED',
            'is_cancelled' => $pago->payment_status === 'CANCELLED',
            'is_review' => $pago->payment_status === 'REVIEW',
            'venta_estado' => $pago->venta->estado_pago ?? null,
        ]);
    }
}
