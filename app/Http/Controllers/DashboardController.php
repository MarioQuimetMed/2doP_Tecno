<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Viaje;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
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
        // Cachear estadísticas por 2 minutos para reducir queries
        $stats = Cache::remember('dashboard_propietario_stats', 120, function () {
            return [
                'total_ventas' => Venta::count(),
                'ventas_mes' => Venta::whereMonth('created_at', now()->month)->count(),
                'total_viajes' => Viaje::count(),
                'viajes_disponibles' => Viaje::disponibles()->count(),
            ];
        });

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }

    private function dashboardVendedor(): Response
    {
        $vendedorId = auth()->id();
        
        // Cachear por vendedor específico
        $stats = Cache::remember("dashboard_vendedor_{$vendedorId}", 120, function () use ($vendedorId) {
            return [
                'mis_ventas' => Venta::where('vendedor_id', $vendedorId)->count(),
                'ventas_mes' => Venta::where('vendedor_id', $vendedorId)
                    ->whereMonth('created_at', now()->month)->count(),
                'viajes_disponibles' => Viaje::disponibles()->count(),
            ];
        });

        return Inertia::render('Vendedor/Dashboard', [
            'stats' => $stats,
        ]);
    }

    private function dashboardCliente(): Response
    {
        return Inertia::render('Cliente/Inicio', [
            'mensaje' => 'Página en proceso de desarrollo',
        ]);
    }
}
