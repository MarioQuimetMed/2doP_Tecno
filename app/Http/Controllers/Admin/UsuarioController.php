<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index()
    {
        // Usar paginación en lugar de cargar todos los usuarios
        $usuarios = User::with('rol')->latest()->paginate(15);
        
        return Inertia::render('Admin/Usuarios/Index', [
            'usuarios' => $usuarios
        ]);
    }

    public function create()
    {
        $roles = Rol::all();
        
        return Inertia::render('Admin/Usuarios/Create', [
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'rol_id' => 'required|exists:rols,id',
            'telefono' => 'nullable|string|max:20',
            'ci_nit' => 'nullable|string|max:20',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol_id' => $request->rol_id,
            'telefono' => $request->telefono,
            'ci_nit' => $request->ci_nit,
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        $roles = Rol::all();

        return Inertia::render('Admin/Usuarios/Edit', [
            'usuario' => $usuario,
            'roles' => $roles
        ]);
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
            'rol_id' => 'required|exists:rols,id',
            'telefono' => 'nullable|string|max:20',
            'ci_nit' => 'nullable|string|max:20',
        ]);

        $usuario->update([
            'name' => $request->name,
            'email' => $request->email,
            'rol_id' => $request->rol_id,
            'telefono' => $request->telefono,
            'ci_nit' => $request->ci_nit,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            
            $usuario->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}
