<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDestinoRequest;
use App\Http\Requests\UpdateDestinoRequest;
use App\Models\Destino;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DestinoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Destino::query();

        // Búsqueda
        if ($request->filled('search')) {
            $query->buscar($request->search);
        }

        // Filtro por país
        if ($request->filled('pais')) {
            $query->porPais($request->pais);
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $destinos = $query->withCount('planesViaje')->paginate(10)->withQueryString();

        // Obtener lista única de países para el filtro
        $paises = Destino::distinct()->pluck('pais')->sort()->values();

        return Inertia::render('Admin/Destinos/Index', [
            'destinos' => $destinos,
            'paises' => $paises,
            'filters' => [
                'search' => $request->search,
                'pais' => $request->pais,
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
        return Inertia::render('Admin/Destinos/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDestinoRequest $request)
    {
        Destino::create($request->validated());

        return redirect()->route('destinos.index')
            ->with('success', 'Destino creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Destino $destino)
    {
        $destino->loadCount('planesViaje');
        $destino->load(['planesViaje' => function ($query) {
            $query->withCount('actividadesDiarias')
                  ->latest()
                  ->limit(5);
        }]);

        return Inertia::render('Admin/Destinos/Show', [
            'destino' => $destino,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Destino $destino)
    {
        return Inertia::render('Admin/Destinos/Edit', [
            'destino' => $destino,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDestinoRequest $request, Destino $destino)
    {
        $destino->update($request->validated());

        return redirect()->route('destinos.index')
            ->with('success', 'Destino actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Destino $destino)
    {
        // Verificar si tiene planes de viaje asociados
        if ($destino->planesViaje()->exists()) {
            return back()->with('error', 'No se puede eliminar el destino porque tiene planes de viaje asociados.');
        }

        $destino->delete();

        return redirect()->route('destinos.index')
            ->with('success', 'Destino eliminado exitosamente.');
    }
}
