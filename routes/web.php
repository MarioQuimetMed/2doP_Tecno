<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PreferenciaController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\DestinoController;
use App\Http\Controllers\Admin\PlanViajeController;
use App\Http\Controllers\Admin\ViajeController;
use App\Http\Controllers\Admin\VentaController;
use App\Http\Controllers\Admin\PlanPagoController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\BitacoraController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Redirigir página de inicio al Login
Route::get('/', function () {
    return redirect()->route('login');
});

// ===== WEBHOOK PAGOFACIL (Sin autenticación) =====
Route::post('/pagofacil/callback', [App\Http\Controllers\PagoFacilCallbackController::class, 'handleCallback'])
    ->name('pagofacil.callback');

// ===== TEST ENDPOINT (Temporal - solo para verificar que ngrok funciona) =====
Route::get('/pagofacil/test', function() {
    return response()->json([
        'message' => '¡Ngrok funciona correctamente!',
        'timestamp' => now()->toDateTimeString(),
        'app' => '2doP_Tecno',
    ]);
})->name('pagofacil.test');

// ===== API PARA POLLING DE ESTADO DE PAGO =====
Route::get('/api/pagos/{pago}/status', [App\Http\Controllers\Api\PagoStatusController::class, 'checkStatus'])
    ->middleware('auth')
    ->name('api.pagos.status');


// Rutas Autenticadas
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard (Redirección inteligente según rol)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ===== BÚSQUEDA GLOBAL (Req. 9) =====
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== PREFERENCIAS DE USUARIO (Temas, Accesibilidad) =====
    Route::post('/preferencias', [PreferenciaController::class, 'update'])->name('preferencias.update');
    Route::get('/preferencias', [PreferenciaController::class, 'show'])->name('preferencias.show');
    Route::get('/temas', [PreferenciaController::class, 'temas'])->name('temas.index');

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
        
        // Gestión de Pagos (CRUD Completo)
        Route::get('/pagos/estadisticas', [PagoController::class, 'estadisticas'])->name('pagos.estadisticas');
        Route::get('/pagos/electronico', [PagoController::class, 'pagoElectronico'])->name('pagos.electronico');
        Route::post('/pagos/procesar-electronico', [PagoController::class, 'procesarPagoElectronico'])->name('pagos.procesar-electronico');
        
        // Pagos con QR (PagoFácil)
        Route::post('/ventas/{venta}/generar-qr', [App\Http\Controllers\Admin\PagoQRController::class, 'generarQR'])->name('pagos.generar-qr');
        Route::get('/pagos/{pago}/consultar-estado', [App\Http\Controllers\Admin\PagoQRController::class, 'consultarEstado'])->name('pagos.consultar-estado');
        Route::get('/pagos/{pago}/mostrar-qr', [App\Http\Controllers\Admin\PagoQRController::class, 'mostrarQR'])->name('pagos.mostrar-qr');
        
        Route::get('/pagos/{pago}/comprobante', [PagoController::class, 'generarComprobante'])->name('pagos.comprobante');
        Route::get('/pagos/historial/{venta}', [PagoController::class, 'historialPorVenta'])->name('pagos.historial');
        Route::resource('pagos', PagoController::class)->only(['index', 'create', 'store', 'show']);
        
        // ===== REPORTES Y ESTADÍSTICAS (CU8) =====
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/', [ReporteController::class, 'index'])->name('index');
            Route::get('/ventas-periodo', [ReporteController::class, 'ventasPorPeriodo'])->name('ventas-periodo');
            Route::get('/destinos-populares', [ReporteController::class, 'destinosPopulares'])->name('destinos-populares');
            Route::get('/ocupacion-viajes', [ReporteController::class, 'ocupacionViajes'])->name('ocupacion-viajes');
            Route::get('/pagos-pendientes', [ReporteController::class, 'pagosPendientes'])->name('pagos-pendientes');
            Route::get('/ventas-vendedor', [ReporteController::class, 'ventasPorVendedor'])->name('ventas-vendedor');
            
            // Exportaciones
            Route::get('/exportar-ventas', [ReporteController::class, 'exportarVentas'])->name('exportar-ventas');
            Route::get('/exportar-pagos-excel', [ReporteController::class, 'exportarPagos'])->name('exportar-pagos-excel');
            Route::get('/exportar-ocupacion', [ReporteController::class, 'exportarOcupacion'])->name('exportar-ocupacion');
        });
        
        // ===== BITÁCORA DE ACCESOS (CU8.9) =====
        Route::get('/bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
        Route::get('/bitacora/exportar', [BitacoraController::class, 'exportar'])->name('bitacora.exportar');
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
