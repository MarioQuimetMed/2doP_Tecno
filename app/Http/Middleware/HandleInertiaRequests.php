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
        return [
            ...parent::share($request),
            // Usar lazy loading para auth data (solo se ejecuta si la página lo necesita)
            'auth' => fn () => $this->getAuthData($request->user()),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            // ELIMINADO: visitas (no es necesario en cada página)
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

        // Cachear el menú por usuario y rol (30 minutos - aumentado)
        $cacheKey = "menu_user_{$user->id}_rol_{$user->rol_id}";
        
        $menu = Cache::remember($cacheKey, 1800, function () use ($user) {
            return $this->loadUserMenu($user);
        });

        // Cachear preferencias (30 minutos)
        $preferenciasCacheKey = "pref_user_{$user->id}";
        $preferencias = Cache::remember($preferenciasCacheKey, 1800, function () use ($user) {
            return $this->loadUserPreferences($user);
        });

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'rol_id' => $user->rol_id,
            ],
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
        // Menú específico para Cliente (Hardcoded por ahora para asegurar funcionalidad)
        if ($user->rol && $user->rol->nombre === 'Cliente') {
            return [
                [
                    'id' => 900,
                    'titulo' => 'Inicio',
                    'ruta' => 'cliente.inicio',
                    'icono' => 'HomeIcon',
                    'hijos' => [],
                ],
                [
                    'id' => 901,
                    'titulo' => 'Mis Cuotas',
                    'ruta' => 'cliente.cuotas.index',
                    'icono' => 'CurrencyDollarIcon',
                    'hijos' => [],
                ],
            ];
        }

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
                  ->orderBy('orden')
                  ->select(['id', 'menu_id', 'parent_id', 'titulo', 'ruta', 'icono', 'orden', 'activo', 'rol_id']); // Solo columnas necesarias
            }])
            ->orderBy('orden')
            ->select(['id', 'menu_id', 'parent_id', 'titulo', 'ruta', 'icono', 'orden', 'activo', 'rol_id']) // Solo columnas necesarias
            ->get()
            ->toArray();
    }

    /**
     * Cargar preferencias del usuario (temas, accesibilidad)
     */
    private function loadUserPreferences($user): ?array
    {
        $preferencia = PreferenciaUsuario::with('tema:id,nombre,css_variables,tipo')
            ->where('user_id', $user->id)
            ->select(['id', 'user_id', 'tema_id', 'tamaño_fuente', 'alto_contraste', 'modo_oscuro_auto'])
            ->first();

        if (!$preferencia) {
            // Devolver preferencias por defecto sin consultar BD
            return [
                'tema' => null,
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
}
