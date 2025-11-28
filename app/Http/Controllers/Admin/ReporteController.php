<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Viaje;
use App\Models\Destino;
use App\Models\Pago;
use App\Models\Cuota;
use App\Models\User;
use App\Models\PlanViaje;
use App\Enums\EstadoPago;
use App\Enums\EstadoCuota;
use App\Enums\EstadoViaje;
use App\Exports\VentasExport;
use App\Exports\PagosExport;
use App\Exports\OcupacionViajesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReporteController extends Controller
{
    /**
     * Página principal de reportes
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Reportes/Index', [
            'resumen' => $this->obtenerResumenGeneral(),
        ]);
    }

    /**
     * Reporte de ventas por período
     */
    public function ventasPorPeriodo(Request $request): Response
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subMonths(6)->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));
        $agrupacion = $request->input('agrupacion', 'mes'); // dia, semana, mes

        $ventas = $this->obtenerVentasPorPeriodo($fechaInicio, $fechaFin, $agrupacion);
        $estadisticas = $this->obtenerEstadisticasVentas($fechaInicio, $fechaFin);

        return Inertia::render('Admin/Reportes/VentasPorPeriodo', [
            'ventas' => $ventas,
            'estadisticas' => $estadisticas,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'agrupacion' => $agrupacion,
            ],
        ]);
    }

    /**
     * Reporte de destinos populares
     */
    public function destinosPopulares(Request $request): Response
    {
        $limite = $request->input('limite', 10);
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $destinos = $this->obtenerDestinosPopulares($limite, $fechaInicio, $fechaFin);
        $evolucion = $this->obtenerEvolucionDestinos($fechaInicio, $fechaFin);

        return Inertia::render('Admin/Reportes/DestinosPopulares', [
            'destinos' => $destinos,
            'evolucion' => $evolucion,
            'filtros' => [
                'limite' => $limite,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }

    /**
     * Reporte de ocupación de viajes
     */
    public function ocupacionViajes(Request $request): Response
    {
        $estado = $request->input('estado');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $viajes = Viaje::with(['planViaje.destino'])
            ->when($estado, fn($q) => $q->where('estado_viaje', $estado))
            ->when($fechaInicio, fn($q) => $q->where('fecha_salida', '>=', $fechaInicio))
            ->when($fechaFin, fn($q) => $q->where('fecha_salida', '<=', $fechaFin))
            ->orderBy('fecha_salida', 'desc')
            ->get()
            ->map(function ($viaje) {
                return [
                    'id' => $viaje->id,
                    'plan_viaje' => $viaje->planViaje->nombre ?? 'N/A',
                    'destino' => $viaje->planViaje->destino->nombre_completo ?? 'N/A',
                    'fecha_salida' => $viaje->fecha_salida->format('d/m/Y'),
                    'fecha_retorno' => $viaje->fecha_retorno->format('d/m/Y'),
                    'cupos_totales' => $viaje->cupos_totales,
                    'cupos_disponibles' => $viaje->cupos_disponibles,
                    'cupos_vendidos' => $viaje->cupos_vendidos,
                    'porcentaje_ocupacion' => $viaje->porcentaje_ocupacion,
                    'estado' => $viaje->estado_viaje->value,
                    'estado_label' => $viaje->estado_viaje->label(),
                ];
            });

        $estadisticas = $this->obtenerEstadisticasOcupacion();

        return Inertia::render('Admin/Reportes/OcupacionViajes', [
            'viajes' => $viajes,
            'estadisticas' => $estadisticas,
            'estados' => EstadoViaje::options(),
            'filtros' => [
                'estado' => $estado,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }

    /**
     * Reporte de pagos pendientes / cuotas vencidas
     */
    public function pagosPendientes(Request $request): Response
    {
        $tipo = $request->input('tipo', 'todos'); // todos, vencidas, proximas

        $cuotasQuery = Cuota::with(['planPago.venta.cliente', 'planPago.venta.viaje.planViaje']);

        if ($tipo === 'vencidas') {
            $cuotasQuery->vencidas();
        } elseif ($tipo === 'proximas') {
            $cuotasQuery->proximasAVencer(15);
        } else {
            $cuotasQuery->pendientes();
        }

        $cuotas = $cuotasQuery->orderBy('fecha_vencimiento')->get()->map(function ($cuota) {
            return [
                'id' => $cuota->id,
                'numero_cuota' => $cuota->numero_cuota,
                'fecha_vencimiento' => $cuota->fecha_vencimiento->format('d/m/Y'),
                'dias_vencimiento' => $cuota->dias_hasta_vencimiento,
                'monto' => $cuota->monto_total_cuota,
                'monto_pagado' => $cuota->montoPagado(),
                'saldo_pendiente' => $cuota->saldoPendiente(),
                'estado' => $cuota->estado_cuota->value,
                'estado_label' => $cuota->estado_cuota->label(),
                'cliente' => $cuota->planPago->venta->cliente->name ?? 'N/A',
                'viaje' => $cuota->planPago->venta->viaje->planViaje->nombre ?? 'N/A',
                'venta_id' => $cuota->planPago->venta_id,
                'es_vencida' => $cuota->dias_hasta_vencimiento < 0,
            ];
        });

        $estadisticas = [
            'total_pendiente' => $cuotas->sum('saldo_pendiente'),
            'cuotas_vencidas' => $cuotas->where('es_vencida', true)->count(),
            'cuotas_proximas' => $cuotas->whereBetween('dias_vencimiento', [0, 15])->count(),
            'total_cuotas' => $cuotas->count(),
        ];

        return Inertia::render('Admin/Reportes/PagosPendientes', [
            'cuotas' => $cuotas,
            'estadisticas' => $estadisticas,
            'filtros' => ['tipo' => $tipo],
        ]);
    }

    /**
     * Reporte de ventas por vendedor
     */
    public function ventasPorVendedor(Request $request): Response
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfYear()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $vendedores = User::whereHas('rol', fn($q) => $q->where('nombre', 'Vendedor'))
            ->withCount(['ventasComoVendedor as total_ventas' => function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_venta', [$fechaInicio, $fechaFin]);
            }])
            ->withSum(['ventasComoVendedor as monto_total' => function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_venta', [$fechaInicio, $fechaFin]);
            }], 'monto_total')
            ->get();

        $totalIngresosGlobal = $vendedores->sum('monto_total') ?: 1;

        $vendedores = $vendedores->map(function ($vendedor) use ($fechaInicio, $fechaFin, $totalIngresosGlobal) {
            $totalVentas = $vendedor->total_ventas ?? 0;
            $totalIngresos = $vendedor->monto_total ?? 0;
            $ticketPromedio = $totalVentas > 0 ? round($totalIngresos / $totalVentas, 2) : 0;
            $porcentajeTotal = round(($totalIngresos / $totalIngresosGlobal) * 100, 1);
                    
            return [
                'id' => $vendedor->id,
                'nombre' => $vendedor->name,
                'email' => $vendedor->email,
                'total_ventas' => $totalVentas,
                'total_ingresos' => $totalIngresos,
                'ticket_promedio' => $ticketPromedio,
                'porcentaje_total' => $porcentajeTotal,
            ];
        })
        ->sortByDesc('total_ingresos')
        ->values();

        $totales = [
            'total_ventas' => $vendedores->sum('total_ventas'),
            'total_ingresos' => $vendedores->sum('total_ingresos'),
            'promedio_vendedor' => $vendedores->count() > 0 
                ? round($vendedores->avg('total_ingresos'), 2) 
                : 0,
        ];

        return Inertia::render('Admin/Reportes/VentasPorVendedor', [
            'vendedores' => $vendedores,
            'totales' => $totales,
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }

    /**
     * Exportar ventas a Excel o PDF
     */
    public function exportarVentas(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));
        $formato = $request->input('formato', 'excel');

        if ($formato === 'pdf') {
            $ventas = Venta::with(['cliente', 'vendedor', 'viaje.planViaje.destino'])
                ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
                ->orderBy('fecha_venta', 'desc')
                ->get();

            $estadisticas = $this->obtenerEstadisticasVentas($fechaInicio, $fechaFin);

            $pdf = Pdf::loadView('pdf.reporte-ventas', [
                'ventas' => $ventas,
                'estadisticas' => $estadisticas,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'fecha_generacion' => now()->format('d/m/Y H:i'),
            ]);

            return $pdf->download("reporte_ventas_{$fechaInicio}_{$fechaFin}.pdf");
        }

        return Excel::download(
            new VentasExport($fechaInicio, $fechaFin),
            "ventas_{$fechaInicio}_{$fechaFin}.xlsx"
        );
    }

    /**
     * Exportar ocupación de viajes a Excel
     */
    public function exportarOcupacion(Request $request)
    {
        $estado = $request->input('estado');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        return Excel::download(
            new OcupacionViajesExport($estado, $fechaInicio, $fechaFin),
            "ocupacion_viajes_" . now()->format('Y-m-d') . ".xlsx"
        );
    }

    /**
     * Exportar pagos pendientes a Excel
     */
    public function exportarPagos(Request $request)
    {
        $tipo = $request->input('tipo', 'todos');

        return Excel::download(
            new PagosExport($tipo),
            "pagos_pendientes_" . now()->format('Y-m-d') . ".xlsx"
        );
    }

    // ===== MÉTODOS PRIVADOS =====

    private function obtenerResumenGeneral(): array
    {
        return Cache::remember('reportes_resumen_general', 300, function () {
            return [
                'ventas_totales' => Venta::count(),
                'ventas_mes' => Venta::whereMonth('created_at', now()->month)->count(),
                'ingresos_totales' => Venta::sum('monto_total'),
                'ingresos_mes' => Venta::whereMonth('created_at', now()->month)->sum('monto_total'),
                'viajes_activos' => Viaje::whereIn('estado_viaje', [EstadoViaje::ABIERTO->value, EstadoViaje::LLENO->value])->count(),
                'ocupacion_promedio' => $this->calcularOcupacionPromedio(),
                'cuotas_vencidas' => Cuota::vencidas()->count(),
                'pagos_pendientes' => Venta::pendientes()->sum('monto_total') - Pago::sum('monto_pagado'),
            ];
        });
    }

    private function obtenerVentasPorPeriodo(string $fechaInicio, string $fechaFin, string $agrupacion): array
    {
        $formato = match($agrupacion) {
            'dia' => 'Y-m-d',
            'semana' => 'Y-W',
            default => 'Y-m',
        };

        $selectRaw = match($agrupacion) {
            'dia' => "DATE(fecha_venta) as periodo",
            'semana' => "CONCAT(EXTRACT(YEAR FROM fecha_venta), '-', LPAD(EXTRACT(WEEK FROM fecha_venta)::text, 2, '0')) as periodo",
            default => "TO_CHAR(fecha_venta, 'YYYY-MM') as periodo",
        };

        $ventas = Venta::selectRaw("$selectRaw, COUNT(*) as cantidad, SUM(monto_total) as monto")
            ->whereBetween('fecha_venta', [$fechaInicio, $fechaFin])
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        return $ventas->map(function ($item) use ($agrupacion) {
            $label = $item->periodo;
            if ($agrupacion === 'mes') {
                $label = Carbon::createFromFormat('Y-m', $item->periodo)->format('M Y');
            } elseif ($agrupacion === 'dia') {
                $label = Carbon::parse($item->periodo)->format('d M');
            }

            return [
                'periodo' => $item->periodo,
                'label' => $label,
                'cantidad' => $item->cantidad,
                'monto' => round($item->monto, 2),
            ];
        })->toArray();
    }

    private function obtenerEstadisticasVentas(string $fechaInicio, string $fechaFin): array
    {
        $ventas = Venta::whereBetween('fecha_venta', [$fechaInicio, $fechaFin]);

        return [
            'total_ventas' => $ventas->count(),
            'monto_total' => round($ventas->sum('monto_total'), 2),
            'promedio_venta' => round($ventas->avg('monto_total') ?? 0, 2),
            'ventas_contado' => $ventas->clone()->alContado()->count(),
            'ventas_credito' => $ventas->clone()->aCredito()->count(),
            'completadas' => $ventas->clone()->completadas()->count(),
            'pendientes' => $ventas->clone()->pendientes()->count(),
        ];
    }

    private function obtenerDestinosPopulares(int $limite, ?string $fechaInicio, ?string $fechaFin): array
    {
        $query = DB::table('destinos')
            ->join('plan_viajes', 'destinos.id', '=', 'plan_viajes.destino_id')
            ->join('viajes', 'plan_viajes.id', '=', 'viajes.plan_viaje_id')
            ->join('ventas', 'viajes.id', '=', 'ventas.viaje_id')
            ->select(
                'destinos.id',
                'destinos.nombre_lugar',
                'destinos.ciudad',
                'destinos.pais',
                DB::raw('COUNT(ventas.id) as total_ventas'),
                DB::raw('SUM(ventas.monto_total) as ingresos_totales')
            );

        if ($fechaInicio) {
            $query->where('ventas.fecha_venta', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->where('ventas.fecha_venta', '<=', $fechaFin);
        }

        return $query->groupBy('destinos.id', 'destinos.nombre_lugar', 'destinos.ciudad', 'destinos.pais')
            ->orderByDesc('total_ventas')
            ->limit($limite)
            ->get()
            ->map(function ($destino) {
                return [
                    'id' => $destino->id,
                    'nombre' => $destino->nombre_lugar,
                    'ubicacion' => "{$destino->ciudad}, {$destino->pais}",
                    'total_ventas' => $destino->total_ventas,
                    'ingresos' => round($destino->ingresos_totales, 2),
                ];
            })
            ->toArray();
    }

    private function obtenerEvolucionDestinos(?string $fechaInicio, ?string $fechaFin): array
    {
        $query = DB::table('destinos')
            ->join('plan_viajes', 'destinos.id', '=', 'plan_viajes.destino_id')
            ->join('viajes', 'plan_viajes.id', '=', 'viajes.plan_viaje_id')
            ->join('ventas', 'viajes.id', '=', 'ventas.viaje_id')
            ->select(
                'destinos.nombre_lugar',
                DB::raw("TO_CHAR(ventas.fecha_venta, 'YYYY-MM') as mes"),
                DB::raw('COUNT(ventas.id) as ventas')
            );

        if ($fechaInicio) {
            $query->where('ventas.fecha_venta', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->where('ventas.fecha_venta', '<=', $fechaFin);
        }

        $data = $query->groupBy('destinos.nombre_lugar', 'mes')
            ->orderBy('mes')
            ->get();

        // Transformar para Chart.js
        $destinos = $data->pluck('nombre_lugar')->unique()->values();
        $meses = $data->pluck('mes')->unique()->sort()->values();

        $datasets = [];
        foreach ($destinos as $destino) {
            $ventasPorMes = [];
            foreach ($meses as $mes) {
                $venta = $data->where('nombre_lugar', $destino)->where('mes', $mes)->first();
                $ventasPorMes[] = $venta ? $venta->ventas : 0;
            }
            $datasets[] = [
                'label' => $destino,
                'data' => $ventasPorMes,
            ];
        }

        return [
            'labels' => $meses->map(fn($m) => Carbon::createFromFormat('Y-m', $m)->format('M Y'))->toArray(),
            'datasets' => array_slice($datasets, 0, 5), // Top 5 destinos
        ];
    }

    private function obtenerEstadisticasOcupacion(): array
    {
        $viajes = Viaje::all();
        
        return [
            'ocupacion_promedio' => $this->calcularOcupacionPromedio(),
            'viajes_llenos' => $viajes->where('estado_viaje', EstadoViaje::LLENO)->count(),
            'viajes_disponibles' => $viajes->where('cupos_disponibles', '>', 0)->count(),
            'cupos_vendidos' => $viajes->sum('cupos_vendidos'),
            'cupos_totales' => $viajes->sum('cupos_totales'),
        ];
    }

    private function calcularOcupacionPromedio(): float
    {
        $viajes = Viaje::whereIn('estado_viaje', [
            EstadoViaje::ABIERTO->value,
            EstadoViaje::LLENO->value,
            EstadoViaje::EN_CURSO->value,
        ])->get();

        if ($viajes->isEmpty()) {
            return 0;
        }

        return round($viajes->avg('porcentaje_ocupacion'), 2);
    }
}
