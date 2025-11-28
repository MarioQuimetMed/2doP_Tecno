<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanPago;
use App\Models\Cuota;
use App\Models\Venta;
use App\Models\Pago;
use App\Enums\EstadoCuota;
use App\Enums\EstadoPago;
use App\Enums\MetodoPago;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PlanPagoController extends Controller
{
    /**
     * Mostrar todos los planes de pago (ventas a crédito)
     */
    public function index(Request $request)
    {
        // Actualizar cuotas vencidas automáticamente
        $this->actualizarCuotasVencidas();
        
        $query = PlanPago::with([
            'venta.cliente',
            'venta.viaje.planViaje.destino',
            'cuotas'
        ])->whereHas('venta');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('venta.cliente', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('estado')) {
            $estado = $request->estado;
            $query->whereHas('venta', function ($q) use ($estado) {
                $q->where('estado_pago', $estado);
            });
        }

        if ($request->filled('cuotas')) {
            $query->where('cantidad_cuotas', $request->cuotas);
        }

        if ($request->filled('con_vencidas')) {
            $query->whereHas('cuotas', function ($q) {
                $q->where('estado_cuota', EstadoCuota::VENCIDO->value)
                  ->orWhere(function ($subQ) {
                      $subQ->where('estado_cuota', EstadoCuota::PENDIENTE->value)
                           ->where('fecha_vencimiento', '<', now());
                  });
            });
        }

        // Ordenar por fecha de venta más reciente
        $query->orderByDesc(
            Venta::select('fecha_venta')
                ->whereColumn('ventas.id', 'plan_pagos.venta_id')
                ->limit(1)
        );

        $planesPago = $query->paginate(10)->withQueryString();

        // Estadísticas
        $stats = $this->calcularEstadisticas();

        return Inertia::render('Admin/PlanesPago/Index', [
            'planesPago' => $planesPago,
            'stats' => $stats,
            'estadosPago' => collect(EstadoPago::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label()
            ]),
            'opcionesCuotas' => [
                ['value' => 3, 'label' => '3 cuotas'],
                ['value' => 6, 'label' => '6 cuotas'],
                ['value' => 12, 'label' => '12 cuotas'],
            ],
            'filters' => $request->only(['search', 'estado', 'cuotas', 'con_vencidas']),
        ]);
    }

    /**
     * Mostrar detalle de un plan de pago con cronograma
     */
    public function show(PlanPago $planPago)
    {
        $planPago->load([
            'venta.cliente',
            'venta.vendedor',
            'venta.viaje.planViaje.destino',
            'venta.pagos' => function ($q) {
                $q->orderBy('fecha_pago', 'desc');
            },
            'cuotas.pagos'
        ]);

        // Actualizar cuotas vencidas
        foreach ($planPago->cuotas as $cuota) {
            if ($cuota->estaVencida()) {
                $cuota->marcarComoVencida();
            }
        }

        // Verificar integridad de datos
        if (!$planPago->venta) {
            abort(404, 'La venta asociada a este plan de pago no existe.');
        }

        // Calcular resumen
        $resumen = [
            'monto_original' => $planPago->venta->monto_total,
            'total_intereses' => $planPago->totalIntereses(),
            'monto_total_con_intereses' => $planPago->montoTotalConIntereses(),
            'total_pagado' => $planPago->venta->montoPagado(),
            'saldo_pendiente' => $planPago->venta->saldoPendiente(),
            'cuotas_pagadas' => $planPago->cuotas->where('estado_cuota', EstadoCuota::PAGADO)->count(),
            'cuotas_pendientes' => $planPago->cuotas->where('estado_cuota', EstadoCuota::PENDIENTE)->count(),
            'cuotas_vencidas' => $planPago->cuotas->where('estado_cuota', EstadoCuota::VENCIDO)->count(),
            'porcentaje_pagado' => $planPago->venta->porcentaje_pagado,
        ];

        return Inertia::render('Admin/PlanesPago/Show', [
            'planPago' => $planPago,
            'resumen' => $resumen,
            'metodosPago' => collect(MetodoPago::cases())->map(fn($m) => [
                'value' => $m->value,
                'label' => $m->label()
            ]),
        ]);
    }

    /**
     * Registrar pago de una cuota específica
     */
    public function pagarCuota(Request $request, Cuota $cuota)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01|max:' . $cuota->saldoPendiente(),
            'metodo_pago' => 'required|in:' . implode(',', array_column(MetodoPago::cases(), 'value')),
            'referencia' => 'nullable|string|max:100',
        ], [
            'monto.required' => 'El monto es requerido.',
            'monto.min' => 'El monto mínimo es $0.01.',
            'monto.max' => 'El monto no puede exceder el saldo pendiente de la cuota.',
            'metodo_pago.required' => 'Seleccione un método de pago.',
        ]);

        // Crear el pago
        $pago = Pago::create([
            'venta_id' => $cuota->planPago->venta_id,
            'cuota_id' => $cuota->id,
            'monto_pagado' => $request->monto,
            'fecha_pago' => now(),
            'metodo_pago' => $request->metodo_pago,
            'referencia_transaccion' => $request->referencia,
        ]);

        // Verificar si la cuota está completamente pagada
        if ($cuota->estaPagada()) {
            $cuota->marcarComoPagada();
        }

        // Actualizar estado de la venta
        $cuota->planPago->venta->actualizarEstadoPago();

        return back()->with('success', 'Pago registrado correctamente.');
    }

    /**
     * Obtener cuotas próximas a vencer (para alertas)
     */
    public function cuotasProximasVencer(Request $request)
    {
        $dias = $request->get('dias', 7);
        
        $cuotas = Cuota::with([
            'planPago.venta.cliente',
            'planPago.venta.viaje.planViaje.destino'
        ])
        ->whereHas('planPago.venta')
        ->proximasAVencer($dias)
        ->orderBy('fecha_vencimiento')
        ->get();

        return response()->json([
            'cuotas' => $cuotas,
            'total' => $cuotas->count(),
        ]);
    }

    /**
     * Obtener cuotas vencidas (para alertas)
     */
    public function cuotasVencidas()
    {
        $this->actualizarCuotasVencidas();
        
        $cuotas = Cuota::with([
            'planPago.venta.cliente',
            'planPago.venta.viaje.planViaje.destino'
        ])
        ->whereHas('planPago.venta')
        ->vencidas()
        ->orderBy('fecha_vencimiento')
        ->get();

        return response()->json([
            'cuotas' => $cuotas,
            'total' => $cuotas->count(),
            'monto_total_vencido' => $cuotas->sum('monto_total_cuota'),
        ]);
    }

    /**
     * Dashboard de planes de pago con alertas
     */
    public function dashboard()
    {
        $this->actualizarCuotasVencidas();
        
        // Cuotas vencidas
        $cuotasVencidas = Cuota::with([
            'planPago.venta.cliente',
            'planPago.venta.viaje.planViaje.destino'
        ])
        ->whereHas('planPago.venta')
        ->vencidas()
        ->orderBy('fecha_vencimiento')
        ->limit(10)
        ->get();

        // Cuotas próximas a vencer (7 días)
        $cuotasProximas = Cuota::with([
            'planPago.venta.cliente',
            'planPago.venta.viaje.planViaje.destino'
        ])
        ->whereHas('planPago.venta')
        ->proximasAVencer(7)
        ->orderBy('fecha_vencimiento')
        ->limit(10)
        ->get();

        // Estadísticas generales
        $stats = $this->calcularEstadisticas();

        return Inertia::render('Admin/PlanesPago/Dashboard', [
            'cuotasVencidas' => $cuotasVencidas,
            'cuotasProximas' => $cuotasProximas,
            'stats' => $stats,
        ]);
    }

    /**
     * Calcular estadísticas de planes de pago
     */
    private function calcularEstadisticas(): array
    {
        $totalPlanes = PlanPago::count();
        
        $planesActivos = PlanPago::whereHas('venta', function ($q) {
            $q->whereIn('estado_pago', [EstadoPago::PENDIENTE->value, EstadoPago::PARCIAL->value]);
        })->count();

        $planesCompletados = PlanPago::whereHas('venta', function ($q) {
            $q->where('estado_pago', EstadoPago::COMPLETADO->value);
        })->count();

        $cuotasVencidas = Cuota::vencidas()->count();
        
        $cuotasProximasVencer = Cuota::proximasAVencer(7)->count();
        
        $montoTotalPendiente = Cuota::whereIn('estado_cuota', [
            EstadoCuota::PENDIENTE->value, 
            EstadoCuota::VENCIDO->value
        ])->sum('monto_total_cuota');

        $montoVencido = Cuota::vencidas()->sum('monto_total_cuota');

        // Recaudación del mes
        $recaudacionMes = Pago::whereMonth('fecha_pago', now()->month)
            ->whereYear('fecha_pago', now()->year)
            ->whereNotNull('cuota_id')
            ->sum('monto_pagado');

        return [
            'total_planes' => $totalPlanes,
            'planes_activos' => $planesActivos,
            'planes_completados' => $planesCompletados,
            'cuotas_vencidas' => $cuotasVencidas,
            'cuotas_proximas_vencer' => $cuotasProximasVencer,
            'monto_total_pendiente' => round($montoTotalPendiente, 2),
            'monto_vencido' => round($montoVencido, 2),
            'recaudacion_mes' => round($recaudacionMes, 2),
        ];
    }

    /**
     * Actualizar estado de cuotas vencidas
     */
    private function actualizarCuotasVencidas(): void
    {
        Cuota::where('estado_cuota', EstadoCuota::PENDIENTE->value)
            ->where('fecha_vencimiento', '<', now()->startOfDay())
            ->update(['estado_cuota' => EstadoCuota::VENCIDO->value]);
    }

    /**
     * Generar reporte de cobranzas
     */
    public function reporteCobranzas(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth());
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth());

        $pagosCredito = Pago::with(['venta.cliente', 'cuota'])
            ->whereNotNull('cuota_id')
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_pago', 'desc')
            ->get();

        $totalRecaudado = $pagosCredito->sum('monto_pagado');
        $cantidadPagos = $pagosCredito->count();

        return Inertia::render('Admin/PlanesPago/ReporteCobranzas', [
            'pagos' => $pagosCredito,
            'totalRecaudado' => $totalRecaudado,
            'cantidadPagos' => $cantidadPagos,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ]);
    }
}
