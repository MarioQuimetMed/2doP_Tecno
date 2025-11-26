<?php

namespace App\Http\Controllers;

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
        // Estadísticas para propietario
        $stats = [
            'total_ventas' => \App\Models\Venta::count(),
            'ventas_mes' => \App\Models\Venta::whereMonth('created_at', now()->month)->count(),
            'total_viajes' => \App\Models\Viaje::count(),
            'viajes_disponibles' => \App\Models\Viaje::disponibles()->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }

    private function dashboardVendedor(): Response
    {
        $vendedorId = auth()->id();
        
        // Estadísticas para vendedor
        $stats = [
            'mis_ventas' => \App\Models\Venta::where('vendedor_id', $vendedorId)->count(),
            'ventas_mes' => \App\Models\Venta::where('vendedor_id', $vendedorId)
                ->whereMonth('created_at', now()->month)->count(),
            'viajes_disponibles' => \App\Models\Viaje::disponibles()->count(),
        ];

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
