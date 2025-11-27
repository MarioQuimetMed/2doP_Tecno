<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BitacoraExport;
use Barryvdh\DomPDF\Facade\Pdf;

class BitacoraController extends Controller
{
    /**
     * Mostrar listado de la bitácora
     */
    public function index(Request $request): Response
    {
        $query = Bitacora::with('usuario')
            ->orderBy('created_at', 'desc');

        // Filtro por usuario
        if ($request->filled('usuario_id')) {
            $query->porUsuario($request->usuario_id);
        }

        // Filtro por acción
        if ($request->filled('accion')) {
            $query->porAccion($request->accion);
        }

        // Filtro por tabla
        if ($request->filled('tabla')) {
            $query->porTabla($request->tabla);
        }

        // Filtro por fecha inicio
        if ($request->filled('fecha_inicio')) {
            $query->where('created_at', '>=', $request->fecha_inicio . ' 00:00:00');
        }

        // Filtro por fecha fin
        if ($request->filled('fecha_fin')) {
            $query->where('created_at', '<=', $request->fecha_fin . ' 23:59:59');
        }

        // Búsqueda general
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion', 'ILIKE', "%{$buscar}%")
                  ->orWhere('ip', 'ILIKE', "%{$buscar}%")
                  ->orWhereHas('usuario', fn($q2) => $q2->where('name', 'ILIKE', "%{$buscar}%"));
            });
        }

        $registros = $query->paginate(50)->withQueryString();

        // Obtener usuarios para filtro
        $usuarios = User::select('id', 'name')
            ->orderBy('name')
            ->get();

        // Obtener tablas únicas para filtro
        $tablas = Bitacora::select('tabla')
            ->distinct()
            ->whereNotNull('tabla')
            ->orderBy('tabla')
            ->pluck('tabla');

        // Obtener acciones únicas
        $acciones = Bitacora::select('accion')
            ->distinct()
            ->whereNotNull('accion')
            ->orderBy('accion')
            ->pluck('accion');

        return Inertia::render('Admin/Bitacora/Index', [
            'registros' => $registros,
            'usuarios' => $usuarios,
            'tablas' => $tablas,
            'acciones' => $acciones,
            'filtros' => $request->only(['usuario_id', 'accion', 'tabla', 'fecha_inicio', 'fecha_fin', 'buscar']),
        ]);
    }

    /**
     * Mostrar detalle de un registro de bitácora
     */
    public function show(Bitacora $bitacora): Response
    {
        $bitacora->load('usuario');

        return Inertia::render('Admin/Bitacora/Show', [
            'bitacora' => [
                'id' => $bitacora->id,
                'usuario' => $bitacora->usuario ? [
                    'id' => $bitacora->usuario->id,
                    'name' => $bitacora->usuario->name,
                    'email' => $bitacora->usuario->email,
                ] : null,
                'accion' => $bitacora->accion,
                'tabla' => $bitacora->tabla,
                'registro_id' => $bitacora->registro_id,
                'datos_anteriores' => $bitacora->datos_anteriores,
                'datos_nuevos' => $bitacora->datos_nuevos,
                'ip' => $bitacora->ip,
                'user_agent' => $bitacora->user_agent,
                'descripcion' => $bitacora->descripcion,
                'created_at' => $bitacora->created_at->format('d/m/Y H:i:s'),
            ],
        ]);
    }

    /**
     * Obtener timeline de acciones de un usuario
     */
    public function timeline(User $usuario): Response
    {
        $actividades = Bitacora::where('user_id', $usuario->id)
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'accion' => $item->accion,
                    'tabla' => $item->tabla,
                    'descripcion' => $item->descripcion,
                    'ip' => $item->ip,
                    'fecha' => $item->created_at->format('d/m/Y'),
                    'hora' => $item->created_at->format('H:i:s'),
                    'hace' => $item->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Admin/Bitacora/Timeline', [
            'usuario' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
            ],
            'actividades' => $actividades,
        ]);
    }

    /**
     * Exportar bitácora a Excel (Método principal para la ruta)
     */
    public function exportar(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $accion = $request->input('accion');
        $userId = $request->input('usuario_id');

        // Registrar exportación
        Bitacora::registrar('EXPORT', 'bitacoras', null, null, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'accion' => $accion,
            'usuario_id' => $userId,
        ], 'Exportación de bitácora a Excel');

        return Excel::download(
            new BitacoraExport($fechaInicio, $fechaFin, $accion, $userId),
            'bitacora_' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    /**
     * Exportar bitácora a Excel (Alias)
     */
    public function exportarExcel(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $accion = $request->input('accion');
        $userId = $request->input('user_id');

        // Registrar exportación
        Bitacora::registrar('EXPORT', 'bitacoras', null, null, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'accion' => $accion,
            'user_id' => $userId,
        ], 'Exportación de bitácora a Excel');

        return Excel::download(
            new BitacoraExport($fechaInicio, $fechaFin, $accion, $userId),
            'bitacora_' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    /**
     * Exportar bitácora a PDF
     */
    public function exportarPdf(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subDays(7)->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $bitacoras = Bitacora::with('usuario')
            ->porRangoFechas($fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59')
            ->orderBy('created_at', 'desc')
            ->take(500)
            ->get();

        // Registrar exportación
        Bitacora::registrar('EXPORT', 'bitacoras', null, null, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'formato' => 'PDF',
        ], 'Exportación de bitácora a PDF');

        $pdf = Pdf::loadView('pdf.reporte-bitacora', [
            'bitacoras' => $bitacoras,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
        ]);

        return $pdf->download('bitacora_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Obtener estadísticas de la bitácora para dashboard
     */
    public function estadisticas(): Response
    {
        // Acciones por día (últimos 30 días)
        $accionesPorDia = Bitacora::selectRaw("DATE(created_at) as fecha, COUNT(*) as total")
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(fn($item) => [
                'fecha' => $item->fecha,
                'total' => $item->total,
            ]);

        // Acciones por tipo
        $accionesPorTipo = Bitacora::selectRaw("accion, COUNT(*) as total")
            ->groupBy('accion')
            ->orderByDesc('total')
            ->get()
            ->map(fn($item) => [
                'accion' => $item->accion,
                'total' => $item->total,
            ]);

        // Usuarios más activos
        $usuariosActivos = Bitacora::selectRaw("user_id, COUNT(*) as total")
            ->whereNotNull('user_id')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $usuario = User::find($item->user_id);
                return [
                    'usuario' => $usuario ? $usuario->name : 'Usuario eliminado',
                    'total' => $item->total,
                ];
            });

        // Tablas más modificadas
        $tablasModificadas = Bitacora::selectRaw("tabla, COUNT(*) as total")
            ->whereNotNull('tabla')
            ->whereIn('accion', ['CREATE', 'UPDATE', 'DELETE'])
            ->groupBy('tabla')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        return Inertia::render('Admin/Bitacora/Estadisticas', [
            'accionesPorDia' => $accionesPorDia,
            'accionesPorTipo' => $accionesPorTipo,
            'usuariosActivos' => $usuariosActivos,
            'tablasModificadas' => $tablasModificadas,
        ]);
    }
}
