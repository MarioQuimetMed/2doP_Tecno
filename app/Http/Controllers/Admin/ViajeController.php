<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreViajeRequest;
use App\Http\Requests\UpdateViajeRequest;
use App\Models\Viaje;
use App\Models\PlanViaje;
use App\Enums\EstadoViaje;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ViajeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Viaje::with(['planViaje.destino']);

        // Búsqueda
        if ($request->filled('search')) {
            $query->whereHas('planViaje', function ($q) use ($request) {
                $q->where('nombre', 'ILIKE', "%{$request->search}%");
            })->orWhereHas('planViaje.destino', function ($q) use ($request) {
                $q->where('nombre_lugar', 'ILIKE', "%{$request->search}%")
                  ->orWhere('ciudad', 'ILIKE', "%{$request->search}%");
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado_viaje', $request->estado);
        }

        // Filtro por plan de viaje
        if ($request->filled('plan_viaje_id')) {
            $query->where('plan_viaje_id', $request->plan_viaje_id);
        }

        // Filtro por fechas
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_salida', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_salida', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'fecha_salida');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $viajes = $query->withCount('ventas')
            ->paginate(10)
            ->withQueryString();

        // Planes de viaje para el filtro
        $planesViaje = PlanViaje::with('destino')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'destino_id']);

        // Estados disponibles
        $estados = collect(EstadoViaje::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
            'color' => $e->color(),
        ]);

        // Estadísticas
        $stats = [
            'total' => Viaje::count(),
            'abiertos' => Viaje::where('estado_viaje', EstadoViaje::ABIERTO->value)->count(),
            'en_curso' => Viaje::where('estado_viaje', EstadoViaje::EN_CURSO->value)->count(),
            'proximos' => Viaje::where('fecha_salida', '>=', now())
                ->where('fecha_salida', '<=', now()->addDays(30))
                ->count(),
        ];

        return Inertia::render('Admin/Viajes/Index', [
            'viajes' => $viajes,
            'planesViaje' => $planesViaje,
            'estados' => $estados,
            'stats' => $stats,
            'filters' => [
                'search' => $request->search,
                'estado' => $request->estado,
                'plan_viaje_id' => $request->plan_viaje_id,
                'fecha_desde' => $request->fecha_desde,
                'fecha_hasta' => $request->fecha_hasta,
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $planesViaje = PlanViaje::with('destino')
            ->orderBy('nombre')
            ->get();

        $estados = collect(EstadoViaje::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
            'color' => $e->color(),
        ]);

        // Si viene con un plan preseleccionado
        $planPreseleccionado = null;
        if ($request->filled('plan_viaje_id')) {
            $planPreseleccionado = PlanViaje::with('destino')->find($request->plan_viaje_id);
        }

        return Inertia::render('Admin/Viajes/Create', [
            'planesViaje' => $planesViaje,
            'estados' => $estados,
            'planPreseleccionado' => $planPreseleccionado,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreViajeRequest $request)
    {
        $planViaje = PlanViaje::findOrFail($request->plan_viaje_id);

        // Calcular fecha de retorno basada en la duración del plan
        $fechaSalida = \Carbon\Carbon::parse($request->fecha_salida);
        $fechaRetorno = $fechaSalida->copy()->addDays($planViaje->duracion_dias - 1);

        Viaje::create([
            'plan_viaje_id' => $request->plan_viaje_id,
            'fecha_salida' => $request->fecha_salida,
            'fecha_retorno' => $fechaRetorno,
            'cupos_totales' => $request->cupos_totales,
            'cupos_disponibles' => $request->cupos_totales, // Inicialmente todos disponibles
            'estado_viaje' => EstadoViaje::ABIERTO,
        ]);

        return redirect()->route('viajes.index')
            ->with('success', 'Viaje programado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Viaje $viaje)
    {
        $viaje->load([
            'planViaje.destino',
            'planViaje.actividadesDiarias' => function ($query) {
                $query->orderBy('dia_numero')->orderBy('hora_inicio');
            },
            'ventas' => function ($query) {
                $query->with(['cliente', 'vendedor'])->latest()->limit(10);
            }
        ]);

        // Pasajeros (clientes que compraron para este viaje)
        $pasajeros = $viaje->ventas()
            ->with('cliente')
            ->get()
            ->pluck('cliente')
            ->unique('id')
            ->values();

        // Agrupar actividades por día
        $actividadesPorDia = $viaje->planViaje->actividadesDiarias->groupBy('dia_numero');

        // Estadísticas del viaje
        $stats = [
            'total_ventas' => $viaje->ventas->count(),
            'ingresos_generados' => $viaje->ventas->sum('monto_total'),
            'porcentaje_ocupacion' => $viaje->porcentaje_ocupacion,
        ];

        $estados = collect(EstadoViaje::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
            'color' => $e->color(),
        ]);

        return Inertia::render('Admin/Viajes/Show', [
            'viaje' => $viaje,
            'pasajeros' => $pasajeros,
            'actividadesPorDia' => $actividadesPorDia,
            'stats' => $stats,
            'estados' => $estados,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Viaje $viaje)
    {
        $viaje->load('planViaje.destino');

        $planesViaje = PlanViaje::with('destino')
            ->orderBy('nombre')
            ->get();

        $estados = collect(EstadoViaje::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
            'color' => $e->color(),
        ]);

        return Inertia::render('Admin/Viajes/Edit', [
            'viaje' => $viaje,
            'planesViaje' => $planesViaje,
            'estados' => $estados,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateViajeRequest $request, Viaje $viaje)
    {
        $planViaje = PlanViaje::findOrFail($request->plan_viaje_id);

        // Calcular fecha de retorno si cambió la fecha de salida o el plan
        $fechaSalida = \Carbon\Carbon::parse($request->fecha_salida);
        $fechaRetorno = $fechaSalida->copy()->addDays($planViaje->duracion_dias - 1);

        // Calcular cupos disponibles basado en el cambio de cupos totales
        $cuposVendidos = $viaje->cupos_vendidos;
        $nuevosCuposDisponibles = $request->cupos_totales - $cuposVendidos;

        if ($nuevosCuposDisponibles < 0) {
            return back()->with('error', 'No puede reducir los cupos por debajo de los ya vendidos (' . $cuposVendidos . ').');
        }

        $viaje->update([
            'plan_viaje_id' => $request->plan_viaje_id,
            'fecha_salida' => $request->fecha_salida,
            'fecha_retorno' => $fechaRetorno,
            'cupos_totales' => $request->cupos_totales,
            'cupos_disponibles' => $nuevosCuposDisponibles,
            'estado_viaje' => $request->estado_viaje,
        ]);

        return redirect()->route('viajes.index')
            ->with('success', 'Viaje actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Viaje $viaje)
    {
        // Verificar si tiene ventas asociadas
        if ($viaje->ventas()->exists()) {
            return back()->with('error', 'No se puede eliminar el viaje porque tiene ventas asociadas.');
        }

        $viaje->delete();

        return redirect()->route('viajes.index')
            ->with('success', 'Viaje eliminado exitosamente.');
    }

    /**
     * Cambiar el estado del viaje
     */
    public function cambiarEstado(Request $request, Viaje $viaje)
    {
        $request->validate([
            'estado_viaje' => 'required|in:' . implode(',', array_column(EstadoViaje::cases(), 'value')),
        ]);

        $nuevoEstado = EstadoViaje::from($request->estado_viaje);

        // Validaciones de cambio de estado
        if ($nuevoEstado === EstadoViaje::CANCELADO && $viaje->ventas()->exists()) {
            // Aquí podrías implementar la lógica para cancelar las ventas
            // Por ahora solo mostramos advertencia
        }

        $viaje->update([
            'estado_viaje' => $nuevoEstado,
        ]);

        return back()->with('success', 'Estado del viaje actualizado a ' . $nuevoEstado->label() . '.');
    }

    /**
     * Obtener datos para el calendario de viajes
     */
    public function calendario(Request $request)
    {
        $query = Viaje::with(['planViaje.destino']);

        // Filtrar por rango de fechas del calendario
        if ($request->filled('start')) {
            $query->where('fecha_salida', '>=', $request->start);
        }
        if ($request->filled('end')) {
            $query->where('fecha_retorno', '<=', $request->end);
        }

        $viajes = $query->get()->map(function ($viaje) {
            $colorMap = [
                'ABIERTO' => '#10B981',     // green
                'LLENO' => '#3B82F6',       // blue
                'EN_CURSO' => '#8B5CF6',    // purple
                'FINALIZADO' => '#6B7280',  // gray
                'CANCELADO' => '#EF4444',   // red
            ];

            return [
                'id' => $viaje->id,
                'title' => $viaje->planViaje->nombre . ' (' . $viaje->cupos_disponibles . '/' . $viaje->cupos_totales . ')',
                'start' => $viaje->fecha_salida->format('Y-m-d'),
                'end' => $viaje->fecha_retorno->addDay()->format('Y-m-d'), // FullCalendar necesita día +1 para mostrar inclusive
                'backgroundColor' => $colorMap[$viaje->estado_viaje->value] ?? '#6B7280',
                'borderColor' => $colorMap[$viaje->estado_viaje->value] ?? '#6B7280',
                'extendedProps' => [
                    'destino' => $viaje->planViaje->destino->nombre_lugar,
                    'ciudad' => $viaje->planViaje->destino->ciudad,
                    'estado' => $viaje->estado_viaje->label(),
                    'cupos_disponibles' => $viaje->cupos_disponibles,
                    'cupos_totales' => $viaje->cupos_totales,
                    'porcentaje_ocupacion' => $viaje->porcentaje_ocupacion,
                ],
            ];
        });

        if ($request->wantsJson()) {
            return response()->json($viajes);
        }

        $estados = collect(EstadoViaje::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
            'color' => $e->color(),
        ]);

        return Inertia::render('Admin/Viajes/Calendario', [
            'viajes' => $viajes,
            'estados' => $estados,
        ]);
    }

    /**
     * Lista de pasajeros por viaje
     */
    public function pasajeros(Viaje $viaje)
    {
        $viaje->load('planViaje.destino');

        $ventas = $viaje->ventas()
            ->with(['cliente', 'vendedor', 'pagos'])
            ->get();

        return Inertia::render('Admin/Viajes/Pasajeros', [
            'viaje' => $viaje,
            'ventas' => $ventas,
        ]);
    }
}
