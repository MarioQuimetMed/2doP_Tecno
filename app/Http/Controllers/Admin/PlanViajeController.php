<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanViajeRequest;
use App\Http\Requests\UpdatePlanViajeRequest;
use App\Models\ActividadDiaria;
use App\Models\Destino;
use App\Models\PlanViaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PlanViajeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PlanViaje::with('destino');

        // Búsqueda
        if ($request->filled('search')) {
            $query->buscar($request->search);
        }

        // Filtro por destino
        if ($request->filled('destino_id')) {
            $query->where('destino_id', $request->destino_id);
        }

        // Filtro por duración
        if ($request->filled('duracion')) {
            $query->porDuracion($request->duracion);
        }

        // Filtro por rango de precio
        if ($request->filled('precio_min') && $request->filled('precio_max')) {
            $query->porRangoPrecio($request->precio_min, $request->precio_max);
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        // Manejo especial para ordenar por destino
        if ($sortField === 'destino') {
            $query->join('destinos', 'plan_viajes.destino_id', '=', 'destinos.id')
                  ->orderBy('destinos.nombre_lugar', $sortDirection)
                  ->select('plan_viajes.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $planesViaje = $query->withCount(['actividadesDiarias', 'viajes'])
            ->paginate(10)
            ->withQueryString();

        // Obtener destinos para el filtro
        $destinos = Destino::orderBy('nombre_lugar')->get(['id', 'nombre_lugar', 'ciudad', 'pais']);

        // Estadísticas
        $stats = [
            'total' => PlanViaje::count(),
            'duracion_promedio' => round(PlanViaje::avg('duracion_dias') ?? 0, 1),
            'precio_promedio' => round(PlanViaje::avg('precio_base') ?? 0, 2),
            'con_actividades' => PlanViaje::has('actividadesDiarias')->count(),
        ];

        return Inertia::render('Admin/PlanesViaje/Index', [
            'planesViaje' => $planesViaje,
            'destinos' => $destinos,
            'stats' => $stats,
            'filters' => [
                'search' => $request->search,
                'destino_id' => $request->destino_id,
                'duracion' => $request->duracion,
                'precio_min' => $request->precio_min,
                'precio_max' => $request->precio_max,
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $destinos = Destino::orderBy('nombre_lugar')->get(['id', 'nombre_lugar', 'ciudad', 'pais']);

        return Inertia::render('Admin/PlanesViaje/Create', [
            'destinos' => $destinos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlanViajeRequest $request)
    {
        DB::beginTransaction();

        try {
            // Crear el plan de viaje
            $planViaje = PlanViaje::create([
                'destino_id' => $request->destino_id,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'duracion_dias' => $request->duracion_dias,
                'precio_base' => $request->precio_base,
            ]);

            // Crear las actividades diarias si existen
            if ($request->has('actividades') && is_array($request->actividades)) {
                foreach ($request->actividades as $actividad) {
                    if (!empty($actividad['descripcion_actividad'])) {
                        $planViaje->actividadesDiarias()->create([
                            'dia_numero' => $actividad['dia_numero'],
                            'descripcion_actividad' => $actividad['descripcion_actividad'],
                            'hora_inicio' => $actividad['hora_inicio'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('planes-viaje.index')
                ->with('success', 'Plan de viaje creado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el plan de viaje: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PlanViaje $planesViaje)
    {
        $planesViaje->load([
            'destino',
            'actividadesDiarias' => function ($query) {
                $query->orderBy('dia_numero')->orderBy('hora_inicio');
            },
            'viajes' => function ($query) {
                $query->withCount('ventas')->latest()->limit(5);
            }
        ]);

        // Agrupar actividades por día
        $actividadesPorDia = $planesViaje->actividadesDiarias->groupBy('dia_numero');

        // Estadísticas del plan
        $stats = [
            'total_actividades' => $planesViaje->actividadesDiarias->count(),
            'total_viajes' => $planesViaje->viajes->count(),
            'viajes_activos' => $planesViaje->viajes->where('estado', 'ABIERTO')->count(),
        ];

        return Inertia::render('Admin/PlanesViaje/Show', [
            'planViaje' => $planesViaje,
            'actividadesPorDia' => $actividadesPorDia,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PlanViaje $planesViaje)
    {
        $planesViaje->load(['actividadesDiarias' => function ($query) {
            $query->orderBy('dia_numero')->orderBy('hora_inicio');
        }]);

        $destinos = Destino::orderBy('nombre_lugar')->get(['id', 'nombre_lugar', 'ciudad', 'pais']);

        return Inertia::render('Admin/PlanesViaje/Edit', [
            'planViaje' => $planesViaje,
            'destinos' => $destinos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlanViajeRequest $request, PlanViaje $planesViaje)
    {
        DB::beginTransaction();

        try {
            // Actualizar el plan de viaje
            $planesViaje->update([
                'destino_id' => $request->destino_id,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'duracion_dias' => $request->duracion_dias,
                'precio_base' => $request->precio_base,
            ]);

            // Eliminar actividades existentes y crear nuevas
            $planesViaje->actividadesDiarias()->delete();

            if ($request->has('actividades') && is_array($request->actividades)) {
                foreach ($request->actividades as $actividad) {
                    if (!empty($actividad['descripcion_actividad'])) {
                        $planesViaje->actividadesDiarias()->create([
                            'dia_numero' => $actividad['dia_numero'],
                            'descripcion_actividad' => $actividad['descripcion_actividad'],
                            'hora_inicio' => $actividad['hora_inicio'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('planes-viaje.index')
                ->with('success', 'Plan de viaje actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar el plan de viaje: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlanViaje $planesViaje)
    {
        // Verificar si tiene viajes asociados
        if ($planesViaje->viajes()->exists()) {
            return back()->with('error', 'No se puede eliminar el plan porque tiene viajes programados asociados.');
        }

        DB::beginTransaction();

        try {
            // Eliminar actividades diarias
            $planesViaje->actividadesDiarias()->delete();
            
            // Eliminar el plan
            $planesViaje->delete();

            DB::commit();

            return redirect()->route('planes-viaje.index')
                ->with('success', 'Plan de viaje eliminado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el plan de viaje: ' . $e->getMessage());
        }
    }
}
