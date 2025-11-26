<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
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
        $menu = [];

        if ($user) {
            $user->load('rol');
            
            // Cargar menú dinámico
            $menuPrincipal = \App\Models\Menu::where('nombre', 'Menú Principal')->first();
            
            if ($menuPrincipal) {
                $menu = \App\Models\MenuItem::where('menu_id', $menuPrincipal->id)
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
                    ->get();
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'rol' => $user?->rol?->nombre,
                'menu' => $menu,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
