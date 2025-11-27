<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\DestinoController;
use App\Http\Controllers\Admin\PlanViajeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Redirigir página de inicio al Login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas Autenticadas
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard (Redirección inteligente según rol)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== RUTAS PARA PROPIETARIO =====
    Route::middleware(['role:Propietario'])->group(function () {
        // Gestión de Usuarios y Roles
        Route::resource('usuarios', UsuarioController::class);
        Route::resource('roles', RolController::class)->only(['index']);
        
        // Gestión de Destinos (CRUD Completo)
        Route::resource('destinos', DestinoController::class);
        
        // Gestión de Planes de Viaje (CRUD Completo)
        Route::resource('planes-viaje', PlanViajeController::class)->parameters([
            'planes-viaje' => 'planesViaje'
        ]);
        
        Route::get('/viajes', function() { 
            return Inertia::render('Admin/Placeholder', ['title' => 'Viajes Programados', 'module' => 'Viajes']); 
        })->name('viajes.index');
        
        // Ventas y Finanzas
        Route::get('/ventas', function() { 
            return Inertia::render('Admin/Placeholder', ['title' => 'Gestión de Ventas', 'module' => 'Ventas']); 
        })->name('ventas.index');
        
        Route::get('/pagos', function() { 
            return Inertia::render('Admin/Placeholder', ['title' => 'Gestión de Pagos', 'module' => 'Pagos']); 
        })->name('pagos.index');
        
        Route::get('/bitacora', function() { 
            return Inertia::render('Admin/Placeholder', ['title' => 'Bitácora del Sistema', 'module' => 'Auditoría']); 
        })->name('bitacora.index');
    });

    // ===== RUTAS PARA VENDEDOR =====
    Route::middleware(['role:Vendedor'])->prefix('vendedor')->group(function () {
        Route::get('/mis-ventas', function() { 
            return Inertia::render('Admin/Placeholder', ['title' => 'Mis Ventas', 'module' => 'Ventas']); 
        })->name('ventas.mis-ventas');
        
        Route::get('/viajes-disponibles', function() { 
            return Inertia::render('Admin/Placeholder', ['title' => 'Viajes Disponibles', 'module' => 'Viajes']); 
        })->name('viajes.disponibles');
        
        Route::get('/clientes', function() { 
            return Inertia::render('Admin/Placeholder', ['title' => 'Mis Clientes', 'module' => 'Clientes']); 
        })->name('clientes.index');
    });

    // ===== RUTAS PARA CLIENTE =====
    Route::middleware(['role:Cliente'])->prefix('cliente')->group(function () {
        Route::get('/inicio', function() { return Inertia::render('Cliente/Inicio'); })->name('cliente.inicio');
    });
});

require __DIR__.'/auth.php';
