<?php

namespace App\Http\Middleware;

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
            ];
        }

        // El rol ya viene cargado con $with en el modelo User

        // Cachear el menú por usuario y rol (5 minutos)
        $cacheKey = "menu_user_{$user->id}_rol_{$user->rol_id}";
        
        $menu = Cache::remember($cacheKey, 300, function () use ($user) {
            return $this->loadUserMenu($user);
        });

        return [
            'user' => $user,
            'rol' => $user->rol?->nombre,
            'menu' => $menu,
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
}
