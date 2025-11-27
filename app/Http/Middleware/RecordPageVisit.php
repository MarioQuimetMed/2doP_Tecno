<?php

namespace App\Http\Middleware;

use App\Models\VisitaPagina;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordPageVisit
{
    /**
     * Rutas que no deben registrar visitas
     */
    protected array $excludedRoutes = [
        'api/*',
        'sanctum/*',
        '_debugbar/*',
        'livewire/*',
        'storage/*',
        'favicon.ico',
        'robots.txt',
    ];

    /**
     * Métodos HTTP que deben registrar visitas
     */
    protected array $allowedMethods = ['GET'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Procesar la respuesta primero
        $response = $next($request);

        // Solo registrar visitas para peticiones GET exitosas
        if ($this->shouldRecordVisit($request, $response)) {
            $this->recordVisit($request);
        }

        return $response;
    }

    /**
     * Determinar si se debe registrar la visita
     */
    protected function shouldRecordVisit(Request $request, Response $response): bool
    {
        // Solo métodos GET
        if (!in_array($request->method(), $this->allowedMethods)) {
            return false;
        }

        // Solo respuestas exitosas (2xx)
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return false;
        }

        // No registrar peticiones AJAX/XHR (excepto Inertia)
        if ($request->ajax() && !$request->header('X-Inertia')) {
            return false;
        }

        // No registrar rutas excluidas
        foreach ($this->excludedRoutes as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Registrar la visita en la base de datos
     */
    protected function recordVisit(Request $request): void
    {
        try {
            VisitaPagina::registrar(
                ruta: $request->path(),
                ip: $request->ip(),
                userId: $request->user()?->id,
                userAgent: $request->userAgent()
            );
        } catch (\Exception $e) {
            // Log silencioso para no interrumpir la navegación
            \Log::warning('Error al registrar visita de página: ' . $e->getMessage());
        }
    }
}
