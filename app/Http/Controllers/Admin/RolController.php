<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::withCount('usuarios')->get();
        
        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles
        ]);
    }
}
