<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Viaje;
use App\Models\Cuota;
use App\Models\Destino;
use App\Enums\EstadoViaje;
use App\Enums\TipoPago;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $rolNombre = $user->rol?->nombre;

        // Redirigir según el rol
        return match($rolNombre) {
            'Propietario' => $this->dashboardPropietario(),
            'Vendedor' => $this->dashboardVendedor(),
            'Cliente' => $this->dashboardCliente(),
            default => Inertia::render('Dashboard'),
        };
    }

    private function dashboardPropietario(): Response
    {
        // Estadísticas principales con caché de 2 minutos
        $stats = Cache::remember('dashboard_propietario_stats', 120, function () {
            $viajes = Viaje::all();
            $ocupacionPromedio = 0;
            if ($viajes->count() > 0) {
                $ocupacionPromedio = round($viajes->avg('porcentaje_ocupacion'), 1);
            }
            
            return [
                'total_ventas' => Venta::count(),
                'ventas_mes' => Venta::whereMonth('created_at', now()->month)->count(),
                'ingresos_totales' => Venta::sum('monto_total'),
                'ingresos_mes' => Venta::whereMonth('created_at', now()->month)->sum('monto_total'),
                'total_viajes' => Viaje::count(),
                'viajes_disponibles' => Viaje::disponibles()->count(),
                'ocupacion_promedio' => $ocupacionPromedio,
            ];
        });

        // Ventas por mes (últimos 6 meses)
        $ventasPorMes = Cache::remember('dashboard_ventas_por_mes', 300, function () {
            return Venta::selectRaw("TO_CHAR(fecha_venta, 'YYYY-MM') as periodo, COUNT(*) as cantidad, SUM(monto_total) as monto")
                ->where('fecha_venta', '>=', now()->subMonths(6)->startOfMonth())
                ->groupBy('periodo')
                ->orderBy('periodo')
                ->get()
                ->map(function ($item) {
                    return [
                        'periodo' => $item->periodo,
                        'label' => Carbon::createFromFormat('Y-m', $item->periodo)->format('M Y'),
                        'cantidad' => $item->cantidad,
                        'monto' => round($item->monto, 2),
                    ];
                });
        });

        // Ventas por tipo de pago
        $ventasPorTipo = Cache::remember('dashboard_ventas_por_tipo', 300, function () {
            return [
                'contado' => Venta::where('tipo_pago', TipoPago::CONTADO->value)->count(),
                'credito' => Venta::where('tipo_pago', TipoPago::CREDITO->value)->count(),
            ];
        });

        // Top 5 destinos populares
        $destinosPopulares = Cache::remember('dashboard_destinos_populares', 300, function () {
            return DB::table('destinos')
                ->join('plan_viajes', 'destinos.id', '=', 'plan_viajes.destino_id')
                ->join('viajes', 'plan_viajes.id', '=', 'viajes.plan_viaje_id')
                ->join('ventas', 'viajes.id', '=', 'ventas.viaje_id')
                ->select(
                    'destinos.id',
                    'destinos.nombre_lugar',
                    DB::raw('COUNT(ventas.id) as total_ventas'),
                    DB::raw('SUM(ventas.monto_total) as ingresos')
                )
                ->groupBy('destinos.id', 'destinos.nombre_lugar')
                ->orderByDesc('total_ventas')
                ->limit(5)
                ->get()
                ->map(function ($destino) {
                    return [
                        'id' => $destino->id,
                        'nombre' => $destino->nombre_lugar,
                        'total_ventas' => $destino->total_ventas,
                        'ingresos' => round($destino->ingresos, 2),
                    ];
                });
        });

        // Próximos viajes
        $viajesProximos = Viaje::with(['planViaje.destino'])
            ->where('fecha_salida', '>=', now())
            ->where('fecha_salida', '<=', now()->addDays(30))
            ->orderBy('fecha_salida')
            ->take(5)
            ->get()
            ->map(function ($viaje) {
                return [
                    'id' => $viaje->id,
                    'destino' => $viaje->planViaje->destino->nombre_lugar ?? 'N/A',
                    'fecha_salida' => $viaje->fecha_salida->format('d/m/Y'),
                    'cupos_disponibles' => $viaje->cupos_disponibles,
                    'porcentaje_ocupacion' => $viaje->porcentaje_ocupacion,
                ];
            });

        // Cuotas vencidas o próximas a vencer
        $cuotasVencidas = Cuota::with(['planPago.venta.cliente', 'planPago.venta.viaje.planViaje'])
            ->where(function ($q) {
                $q->vencidas()
                  ->orWhere(function ($q2) {
                      $q2->proximasAVencer(15);
                  });
            })
            ->orderBy('fecha_vencimiento')
            ->take(10)
            ->get()
            ->map(function ($cuota) {
                return [
                    'id' => $cuota->id,
                    'numero_cuota' => $cuota->numero_cuota,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento->format('d/m/Y'),
                    'dias_vencimiento' => $cuota->dias_hasta_vencimiento,
                    'monto' => $cuota->monto_total_cuota,
                    'cliente' => $cuota->planPago->venta->cliente->name ?? 'N/A',
                    'viaje' => $cuota->planPago->venta->viaje->planViaje->nombre ?? 'N/A',
                ];
            });

        // Alertas
        $cuotasVencidasCount = Cuota::vencidas()->count();
        $montoVencido = Cuota::vencidas()->sum('monto_total_cuota');
        
        $alertas = [
            'cuotas_vencidas' => $cuotasVencidasCount,
            'monto_vencido' => $montoVencido,
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'ventasPorMes' => $ventasPorMes,
            'ventasPorTipo' => $ventasPorTipo,
            'destinosPopulares' => $destinosPopulares,
            'viajesProximos' => $viajesProximos,
            'cuotasVencidas' => $cuotasVencidas,
            'alertas' => $alertas,
        ]);
    }

    private function dashboardVendedor(): Response
    {
        $vendedorId = auth()->id();
        
        // Estadísticas del vendedor
        $stats = Cache::remember("dashboard_vendedor_{$vendedorId}", 120, function () use ($vendedorId) {
            return [
                'mis_ventas' => Venta::where('vendedor_id', $vendedorId)->count(),
                'ventas_mes' => Venta::where('vendedor_id', $vendedorId)
                    ->whereMonth('created_at', now()->month)->count(),
                'ingresos_mes' => Venta::where('vendedor_id', $vendedorId)
                    ->whereMonth('created_at', now()->month)->sum('monto_total'),
                'viajes_disponibles' => Viaje::disponibles()->count(),
                'comision_mes' => Venta::where('vendedor_id', $vendedorId)
                    ->whereMonth('created_at', now()->month)->sum('monto_total') * 0.05, // 5% comisión
            ];
        });

        // Ventas del vendedor por mes
        $ventasPorMes = Venta::selectRaw("TO_CHAR(fecha_venta, 'YYYY-MM') as periodo, COUNT(*) as cantidad, SUM(monto_total) as monto")
            ->where('vendedor_id', $vendedorId)
            ->where('fecha_venta', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get()
            ->map(function ($item) {
                return [
                    'periodo' => $item->periodo,
                    'label' => Carbon::createFromFormat('Y-m', $item->periodo)->format('M Y'),
                    'cantidad' => $item->cantidad,
                    'monto' => round($item->monto, 2),
                ];
            });

        // Últimas ventas del vendedor
        $ultimasVentas = Venta::with(['cliente', 'viaje.planViaje.destino'])
            ->where('vendedor_id', $vendedorId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($venta) {
                return [
                    'id' => $venta->id,
                    'cliente' => $venta->cliente->name ?? 'N/A',
                    'destino' => $venta->viaje->planViaje->destino->nombre_lugar ?? 'N/A',
                    'monto' => $venta->monto_total,
                    'estado' => $venta->estado_pago->label(),
                    'fecha' => $venta->fecha_venta->format('d/m/Y'),
                ];
            });

        // Viajes disponibles para vender
        $viajesDisponibles = Viaje::with(['planViaje.destino'])
            ->disponibles()
            ->where('fecha_salida', '>=', now())
            ->orderBy('fecha_salida')
            ->take(5)
            ->get()
            ->map(function ($viaje) {
                return [
                    'id' => $viaje->id,
                    'destino' => $viaje->planViaje->destino->nombre_lugar ?? 'N/A',
                    'fecha_salida' => $viaje->fecha_salida->format('d/m/Y'),
                    'precio' => $viaje->planViaje->precio ?? 0,
                    'cupos_disponibles' => $viaje->cupos_disponibles,
                ];
            });

        return Inertia::render('Vendedor/Dashboard', [
            'stats' => $stats,
            'ventasPorMes' => $ventasPorMes,
            'ultimasVentas' => $ultimasVentas,
            'viajesDisponibles' => $viajesDisponibles,
        ]);
    }

    private function dashboardCliente()
    {
        return redirect()->route('cliente.inicio');
    }
}
