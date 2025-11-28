<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Venta;
use App\Models\Cuota;
use App\Enums\MetodoPago;
use App\Enums\EstadoPago;
use App\Enums\EstadoCuota;
use App\Enums\TipoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pago::with(['venta.cliente', 'venta.viaje.planViaje.destino', 'cuota']);

        // Búsqueda
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('referencia_comprobante', 'ILIKE', "%{$searchTerm}%")
                  ->orWhereHas('venta.cliente', function ($q) use ($searchTerm) {
                      $q->where('name', 'ILIKE', "%{$searchTerm}%")
                        ->orWhere('email', 'ILIKE', "%{$searchTerm}%");
                  });
            });
        }

        // Filtro por método de pago
        if ($request->filled('metodo_pago')) {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        // Filtro por venta
        if ($request->filled('venta_id')) {
            $query->where('venta_id', $request->venta_id);
        }

        // Filtro por fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_pago', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_pago', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'fecha_pago');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $pagos = $query->paginate(15)->withQueryString();

        // Métodos de pago
        $metodosPago = collect(MetodoPago::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
        ]);

        // Estadísticas
        $stats = [
            'total_pagos' => Pago::count(),
            'total_hoy' => Pago::whereDate('fecha_pago', today())->count(),
            'monto_total' => Pago::sum('monto_pagado'),
            'monto_hoy' => Pago::whereDate('fecha_pago', today())->sum('monto_pagado'),
            'por_metodo' => [
                'efectivo' => Pago::where('metodo_pago', MetodoPago::EFECTIVO->value)->sum('monto_pagado'),
                'tarjeta' => Pago::where('metodo_pago', MetodoPago::TARJETA->value)->sum('monto_pagado'),
                'transferencia' => Pago::where('metodo_pago', MetodoPago::TRANSFERENCIA->value)->sum('monto_pagado'),
                'qr' => Pago::where('metodo_pago', MetodoPago::QR->value)->sum('monto_pagado'),
            ],
        ];

        return Inertia::render('Admin/Pagos/Index', [
            'pagos' => $pagos,
            'metodosPago' => $metodosPago,
            'stats' => $stats,
            'filters' => [
                'search' => $request->search,
                'metodo_pago' => $request->metodo_pago,
                'venta_id' => $request->venta_id,
                'fecha_desde' => $request->fecha_desde,
                'fecha_hasta' => $request->fecha_hasta,
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * Muestra el formulario para registrar pago de contado o de cuota
     */
    public function create(Request $request)
    {
        // Obtener ventas pendientes de pago
        $ventas = Venta::with(['cliente', 'viaje.planViaje.destino', 'planPago.cuotas'])
            ->whereIn('estado_pago', [EstadoPago::PENDIENTE->value, EstadoPago::PARCIAL->value])
            ->orderBy('fecha_venta', 'desc')
            ->get()
            ->map(function ($venta) {
                return [
                    'id' => $venta->id,
                    'cliente' => $venta->cliente->name,
                    'cliente_email' => $venta->cliente->email,
                    'viaje' => $venta->viaje->planViaje->nombre ?? 'N/A',
                    'destino' => $venta->viaje->planViaje->destino->nombre_lugar ?? 'N/A',
                    'fecha_venta' => $venta->fecha_venta->format('d/m/Y'),
                    'monto_total' => $venta->monto_total,
                    'monto_pagado' => $venta->montoPagado(),
                    'saldo_pendiente' => $venta->saldoPendiente(),
                    'tipo_pago' => $venta->tipo_pago->value,
                    'tipo_pago_label' => $venta->tipo_pago->label(),
                    'estado_pago' => $venta->estado_pago->value,
                    'es_credito' => $venta->esACredito(),
                    'cuotas_pendientes' => $venta->esACredito() && $venta->planPago 
                        ? $venta->planPago->cuotas->where('estado_cuota', EstadoCuota::PENDIENTE->value)
                            ->merge($venta->planPago->cuotas->where('estado_cuota', EstadoCuota::VENCIDO->value))
                            ->sortBy('numero_cuota')
                            ->map(fn($c) => [
                                'id' => $c->id,
                                'numero_cuota' => $c->numero_cuota,
                                'monto_total_cuota' => $c->monto_total_cuota,
                                'fecha_vencimiento' => $c->fecha_vencimiento->format('d/m/Y'),
                                'estado' => $c->estado_cuota->value,
                                'saldo_pendiente' => $c->saldoPendiente(),
                            ])->values()
                        : [],
                ];
            });

        // Métodos de pago
        $metodosPago = collect(MetodoPago::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
            'requiereReferencia' => $e->requiereReferencia(),
        ]);

        // Si viene con venta preseleccionada
        $ventaPreseleccionada = null;
        if ($request->filled('venta_id')) {
            $ventaPreseleccionada = $ventas->firstWhere('id', (int)$request->venta_id);
        }

        return Inertia::render('Admin/Pagos/Create', [
            'ventas' => $ventas,
            'metodosPago' => $metodosPago,
            'ventaPreseleccionada' => $ventaPreseleccionada,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Registra un nuevo pago (contado o cuota)
     */
    public function store(Request $request)
    {
        $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:' . implode(',', array_column(MetodoPago::cases(), 'value')),
            'referencia' => 'nullable|string|max:100',
            'cuota_id' => 'nullable|exists:cuotas,id',
        ]);

        $venta = Venta::findOrFail($request->venta_id);

        // Verificar que el monto no exceda el saldo pendiente
        if ($request->monto > $venta->saldoPendiente()) {
            return back()->withErrors(['monto' => 'El monto no puede exceder el saldo pendiente de $' . number_format($venta->saldoPendiente(), 2)]);
        }

        try {
            DB::beginTransaction();

            $pago = Pago::create([
                'venta_id' => $request->venta_id,
                'cuota_id' => $request->cuota_id,
                'fecha_pago' => now(),
                'monto_pagado' => $request->monto,
                'metodo_pago' => $request->metodo_pago,
                'referencia_comprobante' => $request->referencia,
            ]);

            DB::commit();

            return redirect()->route('pagos.show', $pago->id)
                ->with('success', 'Pago registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el pago: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Pago $pago)
    {
        $pago->load([
            'venta.cliente',
            'venta.vendedor',
            'venta.viaje.planViaje.destino',
            'cuota',
        ]);

        $metodosPago = collect(MetodoPago::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
        ]);

        return Inertia::render('Admin/Pagos/Show', [
            'pago' => $pago,
            'metodosPago' => $metodosPago,
        ]);
    }

    /**
     * Generar comprobante de pago en PDF
     */
    public function generarComprobante(Pago $pago)
    {
        $pago->load([
            'venta.cliente',
            'venta.vendedor',
            'venta.viaje.planViaje.destino',
            'cuota',
        ]);

        $data = [
            'pago' => $pago,
            'fecha_emision' => now()->format('d/m/Y H:i'),
            'numero_comprobante' => 'PAG-' . str_pad($pago->id, 6, '0', STR_PAD_LEFT),
        ];

        $pdf = Pdf::loadView('pdf.comprobante-pago', $data);
        
        return $pdf->download('comprobante-pago-' . $data['numero_comprobante'] . '.pdf');
    }

    /**
     * Historial de pagos por venta
     */
    public function historialPorVenta(Venta $venta)
    {
        $venta->load([
            'cliente',
            'vendedor',
            'viaje.planViaje.destino',
            'planPago.cuotas',
            'pagos' => function ($query) {
                $query->orderBy('fecha_pago', 'desc');
            },
        ]);

        $metodosPago = collect(MetodoPago::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
        ]);

        // Calcular datos adicionales
        $resumen = [
            'monto_total' => $venta->monto_total,
            'monto_pagado' => $venta->montoPagado(),
            'saldo_pendiente' => $venta->saldoPendiente(),
            'porcentaje_pagado' => $venta->porcentaje_pagado,
            'cantidad_pagos' => $venta->pagos->count(),
        ];

        return Inertia::render('Admin/Pagos/Historial', [
            'venta' => $venta,
            'metodosPago' => $metodosPago,
            'resumen' => $resumen,
        ]);
    }

    /**
     * Vista para simulación de pago electrónico (Gateway Mock)
     */
    public function pagoElectronico(Request $request)
    {
        $ventaId = $request->get('venta_id');
        $cuotaId = $request->get('cuota_id');

        $venta = null;
        $cuota = null;

        if ($ventaId) {
            $venta = Venta::with(['cliente', 'viaje.planViaje.destino'])->find($ventaId);
        }

        if ($cuotaId) {
            $cuota = Cuota::with('planPago.venta.cliente')->find($cuotaId);
            if ($cuota && !$venta) {
                $venta = $cuota->planPago->venta;
                $venta->load(['cliente', 'viaje.planViaje.destino']);
            }
        }

        $metodosPago = collect([MetodoPago::TARJETA, MetodoPago::QR, MetodoPago::TRANSFERENCIA])
            ->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label(),
            ]);

        return Inertia::render('Admin/Pagos/PagoElectronico', [
            'venta' => $venta ? [
                'id' => $venta->id,
                'cliente' => $venta->cliente->name,
                'viaje' => $venta->viaje->planViaje->nombre ?? 'N/A',
                'destino' => $venta->viaje->planViaje->destino->nombre_lugar ?? 'N/A',
                'monto_total' => $venta->monto_total,
                'saldo_pendiente' => $venta->saldoPendiente(),
            ] : null,
            'cuota' => $cuota ? [
                'id' => $cuota->id,
                'numero_cuota' => $cuota->numero_cuota,
                'monto_total_cuota' => $cuota->monto_total_cuota,
                'saldo_pendiente' => $cuota->saldoPendiente(),
            ] : null,
            'metodosPago' => $metodosPago,
        ]);
    }

    /**
     * Procesar pago electrónico (simulación)
     */
    public function procesarPagoElectronico(Request $request)
    {
        $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:' . implode(',', [MetodoPago::TARJETA->value, MetodoPago::QR->value, MetodoPago::TRANSFERENCIA->value]),
            'cuota_id' => 'nullable|exists:cuotas,id',
            // Datos de tarjeta (simulados)
            'numero_tarjeta' => 'required_if:metodo_pago,TARJETA|nullable|string|max:19',
            'nombre_titular' => 'required_if:metodo_pago,TARJETA|nullable|string|max:100',
            'fecha_expiracion' => 'required_if:metodo_pago,TARJETA|nullable|string|max:5',
            'cvv' => 'required_if:metodo_pago,TARJETA|nullable|string|max:4',
        ]);

        $venta = Venta::findOrFail($request->venta_id);

        // Verificar saldo pendiente
        if ($request->monto > $venta->saldoPendiente()) {
            return back()->withErrors(['monto' => 'El monto excede el saldo pendiente.']);
        }

        // Simular procesamiento del pago (Gateway Mock)
        // En producción, aquí se integraría con pasarelas reales como Stripe, PayPal, etc.
        $simulacion = $this->simularGateway($request->metodo_pago, $request->monto);

        if (!$simulacion['success']) {
            return back()->withErrors(['error' => $simulacion['message']]);
        }

        try {
            DB::beginTransaction();

            // Generar referencia de transacción
            $referencia = $simulacion['transaction_id'];

            $pago = Pago::create([
                'venta_id' => $request->venta_id,
                'cuota_id' => $request->cuota_id,
                'fecha_pago' => now(),
                'monto_pagado' => $request->monto,
                'metodo_pago' => $request->metodo_pago,
                'referencia_comprobante' => $referencia,
            ]);

            DB::commit();

            return redirect()->route('pagos.show', $pago->id)
                ->with('success', '¡Pago procesado exitosamente! Referencia: ' . $referencia);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al procesar el pago: ' . $e->getMessage()]);
        }
    }

    /**
     * Simulador de Gateway de Pago (Mock)
     */
    private function simularGateway(string $metodoPago, float $monto): array
    {
        // Simular un pequeño delay de procesamiento
        usleep(500000); // 0.5 segundos

        // Simular posibles escenarios (95% de éxito)
        $exito = rand(1, 100) <= 95;

        if (!$exito) {
            $errores = [
                'Fondos insuficientes',
                'Tarjeta rechazada por el emisor',
                'Error de conexión con el banco',
                'Transacción declinada',
            ];
            return [
                'success' => false,
                'message' => $errores[array_rand($errores)],
                'transaction_id' => null,
            ];
        }

        // Generar ID de transacción único
        $prefijos = [
            MetodoPago::TARJETA->value => 'TRJ',
            MetodoPago::QR->value => 'QRP',
            MetodoPago::TRANSFERENCIA->value => 'TRF',
        ];

        $transactionId = ($prefijos[$metodoPago] ?? 'PAY') . '-' . 
                         strtoupper(substr(md5(uniqid()), 0, 8)) . '-' . 
                         date('Ymd');

        return [
            'success' => true,
            'message' => 'Transacción procesada correctamente',
            'transaction_id' => $transactionId,
            'timestamp' => now()->toISOString(),
            'monto' => $monto,
            'metodo' => $metodoPago,
        ];
    }

    /**
     * Estadísticas de pagos
     */
    public function estadisticas(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        // Pagos por día en el rango
        $pagosPorDia = Pago::selectRaw('DATE(fecha_pago) as fecha, SUM(monto_pagado) as total, COUNT(*) as cantidad')
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Pagos por método
        $pagosPorMetodo = Pago::selectRaw('metodo_pago, SUM(monto_pagado) as total, COUNT(*) as cantidad')
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
            ->groupBy('metodo_pago')
            ->get()
            ->map(function ($item) {
                $metodo = MetodoPago::tryFrom($item->metodo_pago);
                return [
                    'metodo' => $item->metodo_pago,
                    'label' => $metodo?->label() ?? $item->metodo_pago,
                    'total' => $item->total,
                    'cantidad' => $item->cantidad,
                ];
            });

        // Totales generales
        $totales = [
            'monto_total' => Pago::whereBetween('fecha_pago', [$fechaInicio, $fechaFin])->sum('monto_pagado'),
            'cantidad_pagos' => Pago::whereBetween('fecha_pago', [$fechaInicio, $fechaFin])->count(),
            'promedio_pago' => Pago::whereBetween('fecha_pago', [$fechaInicio, $fechaFin])->avg('monto_pagado') ?? 0,
        ];

        return Inertia::render('Admin/Pagos/Estadisticas', [
            'pagosPorDia' => $pagosPorDia,
            'pagosPorMetodo' => $pagosPorMetodo,
            'totales' => $totales,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }
}
