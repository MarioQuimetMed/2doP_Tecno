<?php

namespace App\Http\Middleware;

use App\Models\PreferenciaUsuario;
use App\Models\Tema;
use App\Models\VisitaPagina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        return [
            ...parent::share($request),
            'auth' => fn () => $this->getAuthData($user),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'visitas' => fn () => $this->getVisitasData(),
        ];
    }

    /**
     * Obtener datos de autenticación con caché del menú
     */
    private function getAuthData($user): array
    {
        if (!$user) {
            return [
                'user' => null,
                'rol' => null,
                'menu' => [],
                'preferencias' => null,
            ];
        }

        // El rol ya viene cargado con $with en el modelo User

        // Cachear el menú por usuario y rol (5 minutos)
        $cacheKey = "menu_user_{$user->id}_rol_{$user->rol_id}";
        
        $menu = Cache::remember($cacheKey, 300, function () use ($user) {
            return $this->loadUserMenu($user);
        });

        // Cargar preferencias del usuario
        $preferencias = $this->loadUserPreferences($user);

        return [
            'user' => $user,
            'rol' => $user->rol?->nombre,
            'menu' => $menu,
            'preferencias' => $preferencias,
        ];
    }

    /**
     * Cargar menú del usuario desde la base de datos
     */
    private function loadUserMenu($user): array
    {
        $menuPrincipal = \App\Models\Menu::where('nombre', 'Menú Principal')->first();
        
        if (!$menuPrincipal) {
            return [];
        }

        return \App\Models\MenuItem::where('menu_id', $menuPrincipal->id)
            ->whereNull('parent_id')
            ->where('activo', true)
            ->where(function($q) use ($user) {
                $q->whereNull('rol_id')
                  ->orWhere('rol_id', $user->rol_id);
            })
            ->with(['hijos' => function($q) use ($user) {
                $q->where('activo', true)
                  ->where(function($sq) use ($user) {
                      $sq->whereNull('rol_id')
                        ->orWhere('rol_id', $user->rol_id);
                  })
                  ->orderBy('orden');
            }])
            ->orderBy('orden')
            ->get()
            ->toArray();
    }

    /**
     * Cargar preferencias del usuario (temas, accesibilidad)
     */
    private function loadUserPreferences($user): ?array
    {
        $preferencia = PreferenciaUsuario::with('tema')
            ->where('user_id', $user->id)
            ->first();

        if (!$preferencia) {
            // Devolver preferencias por defecto
            return [
                'tema' => Tema::paraAdultos()?->toArray(),
                'tamaño_fuente' => 'normal',
                'alto_contraste' => false,
                'modo_oscuro_auto' => true,
            ];
        }

        return [
            'tema' => $preferencia->tema?->toArray(),
            'tamaño_fuente' => $preferencia->tamaño_fuente,
            'alto_contraste' => $preferencia->alto_contraste,
            'modo_oscuro_auto' => $preferencia->modo_oscuro_auto,
        ];
    }

    /**
     * Obtener datos de visitas con caché optimizado
     */
    private function getVisitasData(): array
    {
        // Cachear contadores de visitas por 60 segundos para optimizar consultas
        return Cache::remember('visitas_contador', 60, function () {
            return [
                'total' => VisitaPagina::count(),
                'hoy' => VisitaPagina::hoy()->count(),
                'unicas' => VisitaPagina::distinct('ip')->count('ip'),
            ];
        });
    }
}
