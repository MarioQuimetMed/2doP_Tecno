<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\DestinoController;
use App\Http\Controllers\Admin\PlanViajeController;
use App\Http\Controllers\Admin\ViajeController;
use App\Http\Controllers\Admin\VentaController;
use App\Http\Controllers\Admin\PlanPagoController;
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
        
        // Gestión de Viajes Programados (CRUD Completo)
        Route::get('/viajes/calendario', [ViajeController::class, 'calendario'])->name('viajes.calendario');
        Route::get('/viajes/{viaje}/pasajeros', [ViajeController::class, 'pasajeros'])->name('viajes.pasajeros');
        Route::patch('/viajes/{viaje}/cambiar-estado', [ViajeController::class, 'cambiarEstado'])->name('viajes.cambiar-estado');
        Route::resource('viajes', ViajeController::class);
        
        // Gestión de Ventas (CRUD Completo + Pagos + PDF)
        Route::get('/ventas/{venta}/comprobante', [VentaController::class, 'generarComprobante'])->name('ventas.comprobante');
        Route::get('/ventas/{venta}/boleto', [VentaController::class, 'generarBoleto'])->name('ventas.boleto');
        Route::post('/ventas/{venta}/registrar-pago', [VentaController::class, 'registrarPago'])->name('ventas.registrar-pago');
        Route::resource('ventas', VentaController::class);
        
        // Gestión de Planes de Pago (Crédito)
        Route::get('/planes-pago/dashboard', [PlanPagoController::class, 'dashboard'])->name('planes-pago.dashboard');
        Route::post('/planes-pago/cuotas/{cuota}/pagar', [PlanPagoController::class, 'pagarCuota'])->name('planes-pago.pagar-cuota');
        Route::get('/planes-pago/cuotas-vencidas', [PlanPagoController::class, 'cuotasVencidas'])->name('planes-pago.cuotas-vencidas');
        Route::get('/planes-pago/cuotas-proximas', [PlanPagoController::class, 'cuotasProximasVencer'])->name('planes-pago.cuotas-proximas');
        Route::resource('planes-pago', PlanPagoController::class)->only(['index', 'show']);
        
        // Pagos generales (placeholder por ahora)
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
