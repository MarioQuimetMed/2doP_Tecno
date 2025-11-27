<?php

namespace App\Http\Controllers;

use App\Models\Destino;
use App\Models\PlanViaje;
use App\Models\Viaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    /**
     * Búsqueda global en destinos, planes de viaje y viajes
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        // Si la búsqueda está vacía, retornar vacío
        if (strlen($query) < 2) {
            return response()->json([
                'destinos' => [],
                'planes' => [],
                'viajes' => [],
                'total' => 0,
            ]);
        }

        // Cachear resultados por 30 segundos para consultas repetidas
        $cacheKey = 'search_' . md5($query);
        
        $resultados = Cache::remember($cacheKey, 30, function () use ($query) {
            return [
                'destinos' => $this->buscarDestinos($query),
                'planes' => $this->buscarPlanesViaje($query),
                'viajes' => $this->buscarViajes($query),
            ];
        });

        $resultados['total'] = count($resultados['destinos']) 
                             + count($resultados['planes']) 
                             + count($resultados['viajes']);

        return response()->json($resultados);
    }

    /**
     * Buscar en destinos
     */
    private function buscarDestinos(string $query): array
    {
        return Destino::buscar($query)
            ->select('id', 'nombre_lugar', 'ciudad', 'pais', 'descripcion')
            ->limit(5)
            ->get()
            ->map(function ($destino) {
                return [
                    'id' => $destino->id,
                    'titulo' => $destino->nombre_lugar,
                    'subtitulo' => "{$destino->ciudad}, {$destino->pais}",
                    'descripcion' => \Str::limit($destino->descripcion, 80),
                    'tipo' => 'destino',
                    'icono' => 'MapPinIcon',
                    'ruta' => 'destinos.show',
                ];
            })
            ->toArray();
    }

    /**
     * Buscar en planes de viaje
     */
    private function buscarPlanesViaje(string $query): array
    {
        return PlanViaje::buscar($query)
            ->with('destino:id,nombre_lugar,ciudad')
            ->select('id', 'nombre', 'descripcion', 'duracion_dias', 'precio_base', 'destino_id')
            ->limit(5)
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'titulo' => $plan->nombre,
                    'subtitulo' => "{$plan->duracion_dias} días - Bs. " . number_format($plan->precio_base, 2),
                    'descripcion' => \Str::limit($plan->descripcion, 80),
                    'destino' => $plan->destino?->nombre_lugar,
                    'tipo' => 'plan',
                    'icono' => 'ClipboardDocumentListIcon',
                    'ruta' => 'planes-viaje.show',
                ];
            })
            ->toArray();
    }

    /**
     * Buscar en viajes programados
     */
    private function buscarViajes(string $query): array
    {
        return Viaje::with(['planViaje.destino:id,nombre_lugar,ciudad'])
            ->whereHas('planViaje', function ($q) use ($query) {
                $q->where('nombre', 'ILIKE', "%{$query}%");
            })
            ->orWhereHas('planViaje.destino', function ($q) use ($query) {
                $q->where('nombre_lugar', 'ILIKE', "%{$query}%")
                  ->orWhere('ciudad', 'ILIKE', "%{$query}%")
                  ->orWhere('pais', 'ILIKE', "%{$query}%");
            })
            ->where('fecha_salida', '>=', now())
            ->select('id', 'plan_viaje_id', 'fecha_salida', 'fecha_retorno', 'cupos_disponibles', 'estado_viaje')
            ->orderBy('fecha_salida')
            ->limit(5)
            ->get()
            ->map(function ($viaje) {
                return [
                    'id' => $viaje->id,
                    'titulo' => $viaje->planViaje?->nombre ?? 'Viaje',
                    'subtitulo' => "Salida: " . $viaje->fecha_salida->format('d/m/Y') . " - {$viaje->cupos_disponibles} cupos",
                    'descripcion' => $viaje->planViaje?->destino?->nombre_lugar ?? '',
                    'estado' => $viaje->estado_viaje->value,
                    'tipo' => 'viaje',
                    'icono' => 'CalendarIcon',
                    'ruta' => 'viajes.show',
                ];
            })
            ->toArray();
    }
}
