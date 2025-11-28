<?php

namespace App\Http\Controllers;

use App\Models\PreferenciaUsuario;
use App\Models\Tema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreferenciaController extends Controller
{
    /**
     * Actualizar las preferencias del usuario autenticado.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'No autenticado'], 401);
            }
            return back();
        }

        $validated = $request->validate([
            'tema_id' => 'nullable|integer|exists:temas,id',
            'tamaño_fuente' => 'nullable|in:pequeño,normal,grande,extra_grande',
            'alto_contraste' => 'nullable|boolean',
            'modo_oscuro_auto' => 'nullable|boolean',
        ]);

        // Crear o actualizar preferencias
        $preferencia = PreferenciaUsuario::updateOrCreate(
            ['user_id' => $user->id],
            [
                'tema_id' => $validated['tema_id'] ?? null,
                'tamaño_fuente' => $validated['tamaño_fuente'] ?? 'normal',
                'alto_contraste' => $validated['alto_contraste'] ?? false,
                'modo_oscuro_auto' => $validated['modo_oscuro_auto'] ?? true,
            ]
        );

        // Si es una petición AJAX/Inertia, devolver respuesta vacía (sin contenido)
        if ($request->header('X-Inertia')) {
            return back();
        }

        // Si explícitamente quiere JSON
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Preferencias actualizadas correctamente',
                'preferencia' => $preferencia->load('tema'),
            ]);
        }

        // Por defecto, volver a la página anterior sin mostrar nada
        return back();
    }

    /**
     * Obtener las preferencias del usuario autenticado.
     */
    public function show()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $preferencia = PreferenciaUsuario::with('tema')
            ->where('user_id', $user->id)
            ->first();

        // Si no existe, devolver preferencias por defecto
        if (!$preferencia) {
            return response()->json([
                'tema' => Tema::paraAdultos(),
                'tamaño_fuente' => 'normal',
                'alto_contraste' => false,
                'modo_oscuro_auto' => true,
            ]);
        }

        return response()->json($preferencia);
    }

    /**
     * Obtener todos los temas disponibles.
     */
    public function temas()
    {
        $temas = Tema::activos()->get();
        
        return response()->json($temas);
    }
}
