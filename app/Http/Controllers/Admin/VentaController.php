<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Models\Venta;
use App\Models\Viaje;
use App\Models\User;
use App\Models\Rol;
use App\Models\PlanPago;
use App\Models\Pago;
use App\Enums\TipoPago;
use App\Enums\EstadoPago;
use App\Enums\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Venta::with(['cliente', 'vendedor', 'viaje.planViaje.destino'])
            ->withSum('pagos', 'monto_pagado');

        // Búsqueda
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('cliente', function ($q) use ($searchTerm) {
                    $q->where('name', 'ILIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'ILIKE', "%{$searchTerm}%");
                })->orWhereHas('viaje.planViaje', function ($q) use ($searchTerm) {
                    $q->where('nombre', 'ILIKE', "%{$searchTerm}%");
                })->orWhereHas('viaje.planViaje.destino', function ($q) use ($searchTerm) {
                    $q->where('nombre_lugar', 'ILIKE', "%{$searchTerm}%")
                      ->orWhere('ciudad', 'ILIKE', "%{$searchTerm}%");
                });
            });
        }

        // Filtro por estado de pago
        if ($request->filled('estado_pago')) {
            $query->where('estado_pago', $request->estado_pago);
        }

        // Filtro por tipo de pago
        if ($request->filled('tipo_pago')) {
            $query->where('tipo_pago', $request->tipo_pago);
        }

        // Filtro por vendedor
        if ($request->filled('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }

        // Filtro por viaje
        if ($request->filled('viaje_id')) {
            $query->where('viaje_id', $request->viaje_id);
        }

        // Filtro por fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_venta', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'fecha_venta');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $ventas = $query->withCount('pagos')
            ->paginate(10)
            ->withQueryString();

        // Calcular montos pagados para cada venta usando el valor precargado
        $ventas->getCollection()->transform(function ($venta) {
            // Usar el valor precargado por withSum, o 0 si es nulo
            $montoPagado = $venta->pagos_sum_monto_pagado ?? 0;
            
            $venta->monto_pagado = $montoPagado;
            $venta->saldo_pendiente = $venta->monto_total - $montoPagado;
            // Calcular porcentaje manualmente para evitar llamar al accessor que hace query
            $venta->porcentaje_pagado = $venta->monto_total > 0 
                ? round(($montoPagado / $venta->monto_total) * 100, 2) 
                : 0;
                
            return $venta;
        });

        // Vendedores para el filtro
        $rolVendedor = Rol::where('nombre', 'Vendedor')->first();
        $vendedores = User::where('rol_id', $rolVendedor?->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Estados de pago
        $estadosPago = collect(EstadoPago::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
            'color' => $e->color(),
        ]);

        // Tipos de pago
        $tiposPago = collect(TipoPago::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
        ]);

        // Viajes disponibles para filtro
        $viajes = Viaje::with('planViaje.destino')
            ->orderBy('fecha_salida', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'nombre' => $v->planViaje->nombre . ' - ' . $v->fecha_salida->format('d/m/Y'),
            ]);

        // Estadísticas
        $stats = [
            'total' => Venta::count(),
            'pendientes' => Venta::where('estado_pago', EstadoPago::PENDIENTE->value)->count(),
            'completadas' => Venta::where('estado_pago', EstadoPago::COMPLETADO->value)->count(),
            'monto_total' => Venta::where('estado_pago', '!=', EstadoPago::ANULADO->value)->sum('monto_total'),
            'monto_cobrado' => Pago::sum('monto_pagado'),
        ];

        return Inertia::render('Admin/Ventas/Index', [
            'ventas' => $ventas,
            'vendedores' => $vendedores,
            'estadosPago' => $estadosPago,
            'tiposPago' => $tiposPago,
            'viajes' => $viajes,
            'stats' => $stats,
            'filters' => [
                'search' => $request->search,
                'estado_pago' => $request->estado_pago,
                'tipo_pago' => $request->tipo_pago,
                'vendedor_id' => $request->vendedor_id,
                'viaje_id' => $request->viaje_id,
                'fecha_desde' => $request->fecha_desde,
                'fecha_hasta' => $request->fecha_hasta,
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Viajes disponibles (con cupos)
        $viajes = Viaje::with(['planViaje.destino'])
            ->disponibles()
            ->proximos()
            ->get()
            ->map(function ($viaje) {
                return [
                    'id' => $viaje->id,
                    'nombre' => $viaje->planViaje->nombre,
                    'destino' => $viaje->planViaje->destino->nombre_lugar . ', ' . $viaje->planViaje->destino->ciudad,
                    'fecha_salida' => $viaje->fecha_salida->format('Y-m-d'),
                    'fecha_salida_formato' => $viaje->fecha_salida->format('d/m/Y'),
                    'fecha_retorno' => $viaje->fecha_retorno->format('d/m/Y'),
                    'duracion_dias' => $viaje->planViaje->duracion_dias,
                    'precio' => $viaje->planViaje->precio_base,
                    'precio_formateado' => '$' . number_format($viaje->planViaje->precio_base, 2),
                    'cupos_disponibles' => $viaje->cupos_disponibles,
                    'cupos_totales' => $viaje->cupos_totales,
                    'estado' => $viaje->estado_viaje->value,
                    'estado_label' => $viaje->estado_viaje->label(),
                ];
            });

        // Clientes existentes
        $rolCliente = Rol::where('nombre', 'Cliente')->first();
        $clientes = User::where('rol_id', $rolCliente?->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'telefono', 'ci_nit']);

        // Tipos de pago
        $tiposPago = collect(TipoPago::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
        ]);

        // Métodos de pago
        $metodosPago = collect(MetodoPago::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
            'requiereReferencia' => $e->requiereReferencia(),
        ]);

        // Opciones de cuotas para crédito
        $opcionesCuotas = [
            ['cantidad' => 3, 'interes' => 0, 'label' => '3 cuotas (0% interés)'],
            ['cantidad' => 6, 'interes' => 5, 'label' => '6 cuotas (5% interés)'],
            ['cantidad' => 12, 'interes' => 10, 'label' => '12 cuotas (10% interés)'],
        ];

        // Si viene con un viaje preseleccionado
        $viajePreseleccionado = null;
        if ($request->filled('viaje_id')) {
            $viajePreseleccionado = $viajes->firstWhere('id', (int)$request->viaje_id);
        }

        return Inertia::render('Admin/Ventas/Create', [
            'viajes' => $viajes,
            'clientes' => $clientes,
            'tiposPago' => $tiposPago,
            'metodosPago' => $metodosPago,
            'opcionesCuotas' => $opcionesCuotas,
            'viajePreseleccionado' => $viajePreseleccionado,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVentaRequest $request)
    {
        // Aumentar tiempo de ejecución a 120 segundos por la lentitud de la BD remota
        set_time_limit(120);

        $viaje = Viaje::with('planViaje')->findOrFail($request->viaje_id);

        // Verificar cupos disponibles
        if (!$viaje->tieneCuposDisponibles($request->cantidad_personas)) {
            return back()->withErrors(['viaje_id' => 'No hay suficientes cupos disponibles para este viaje.']);
        }

        try {
            DB::beginTransaction();

            // Calcular monto total
            $montoTotal = $viaje->planViaje->precio_base * $request->cantidad_personas;

            // Si es nuevo cliente, crearlo primero
            $clienteId = $request->cliente_id;
            if ($request->filled('nuevo_cliente')) {
                $rolCliente = Rol::where('nombre', 'Cliente')->first();
                $nuevoCliente = User::create([
                    'rol_id' => $rolCliente->id,
                    'name' => $request->nuevo_cliente['nombre'],
                    'email' => $request->nuevo_cliente['email'],
                    'password' => bcrypt($request->nuevo_cliente['ci_nit']), // Contraseña temporal
                    'telefono' => $request->nuevo_cliente['telefono'] ?? null,
                    'ci_nit' => $request->nuevo_cliente['ci_nit'],
                ]);
                $clienteId = $nuevoCliente->id;
            }

            // Crear la venta
            $venta = Venta::create([
                'cliente_id' => $clienteId,
                'vendedor_id' => auth()->id(),
                'viaje_id' => $request->viaje_id,
                'fecha_venta' => now(),
                'monto_total' => $montoTotal,
                'tipo_pago' => $request->tipo_pago,
                'estado_pago' => EstadoPago::PENDIENTE,
            ]);

            // Reservar cupos en el viaje
            $viaje->reservarCupos($request->cantidad_personas);

            // Si es pago a crédito, crear plan de pagos
            if ($request->tipo_pago === TipoPago::CREDITO->value) {
                $opcionCuota = collect([
                    ['cantidad' => 3, 'interes' => 0],
                    ['cantidad' => 6, 'interes' => 5],
                    ['cantidad' => 12, 'interes' => 10],
                ])->firstWhere('cantidad', $request->cantidad_cuotas);

                $planPago = PlanPago::create([
                    'venta_id' => $venta->id,
                    'cantidad_cuotas' => $request->cantidad_cuotas,
                    'tasa_interes' => $opcionCuota['interes'] ?? 0,
                    'dia_vencimiento_mensual' => $request->dia_vencimiento ?? 15,
                ]);

                // Generar las cuotas automáticamente
                $planPago->generarCuotas();
            }

            // Variable para tracking si se debe mostrar QR
            $pagoQRId = null;

            // Si se hizo un pago inicial con QR, generar QR automáticamente
            if ($request->filled('pago_inicial') && $request->pago_inicial['monto'] > 0) {
                if ($request->pago_inicial['metodo'] === MetodoPago::QR->value) {
                    try {
                        // Generar QR para el pago
                        $pagoQRId = $this->generarPagoConQR($venta, $request->pago_inicial['monto']);
                    } catch (\Exception $e) {
                        // Si falla el QR, deshacer todo (venta, cliente, etc.)
                        DB::rollBack();
                        \Illuminate\Support\Facades\Log::error('Error generando QR: ' . $e->getMessage());
                        return back()->withErrors(['error' => 'Error al generar el código QR con PagoFácil: ' . $e->getMessage()]);
                    }
                } else {
                    // Pago tradicional (efectivo, transferencia, etc.)
                    $pago = Pago::create([
                        'venta_id' => $venta->id,
                        'cuota_id' => null, // Pago general
                        'fecha_pago' => now(),
                        'monto_pagado' => $request->pago_inicial['monto'],
                        'metodo_pago' => $request->pago_inicial['metodo'],
                        'referencia_comprobante' => $request->pago_inicial['referencia'] ?? null,
                    ]);
                }
            }

            DB::commit();

            // Si se generó un QR, redirigir a la página de QR
            if ($pagoQRId) {
                return redirect()->route('pagos.mostrar-qr', $pagoQRId)
                    ->with('success', 'Venta creada. Complete el pago escaneando el código QR.');
            }

            // Si no hay QR, redirigir a la venta normalmente
            return redirect()->route('ventas.show', $venta->id)
                ->with('success', 'Venta registrada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error general en venta: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Ocurrió un error inesperado: ' . $e->getMessage()]);
        }
    }

    /**
     * Generar pago con QR usando PagoFácil
     */
    private function generarPagoConQR(Venta $venta, float $monto): int
{
    $pagoFacilService = app(\App\Services\PagoFacilService::class);
    
    // Generar ID único de transacción
    $companyTransactionId = 'grupo15sa_PAGO-' . $venta->id . '-' . \Illuminate\Support\Str::random(8);

    // Crear registro de pago pendiente
    $pago = Pago::create([
        'venta_id' => $venta->id,
        'cuota_id' => null,
        'fecha_pago' => null, // Se llenará cuando se confirme
        'monto_pagado' => $monto,
        'metodo_pago' => MetodoPago::QR->value,
        'company_transaction_id' => $companyTransactionId,
        'payment_status' => 'PENDING',
    ]);

    // Preparar parámetros y generar QR
    $params = $pagoFacilService->prepareQRParams($venta, $monto, $companyTransactionId);
    $qrData = $pagoFacilService->generateQR($params);

    // Actualizar pago con datos del QR
    $pago->update([
        'pagofacil_transaction_id' => $qrData['transactionId'],
        'qr_base64' => $qrData['qrBase64'] ?? null,
        'checkout_url' => $qrData['checkoutUrl'] ?? null,
        'deep_link' => $qrData['deepLink'] ?? null,
        'qr_content_url' => $qrData['qrContentUrl'] ?? null,
        'universal_url' => $qrData['universalUrl'] ?? null,
        'qr_expiration_date' => $qrData['expirationDate'] ?? null,
    ]);

    return $pago->id;
}

/**
 * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        $venta->load([
            'cliente',
            'vendedor',
            'viaje.planViaje.destino',
            'viaje.planViaje.actividadesDiarias' => function ($query) {
                $query->orderBy('dia_numero')->orderBy('hora_inicio');
            },
            'planPago.cuotas' => function ($query) {
                $query->orderBy('numero_cuota');
            },
            'pagos' => function ($query) {
                $query->orderBy('fecha_pago', 'desc');
            },
        ]);

        // Calcular datos adicionales
        $venta->monto_pagado = $venta->montoPagado();
        $venta->saldo_pendiente = $venta->saldoPendiente();
        $venta->porcentaje_pagado = $venta->porcentaje_pagado;

        // Información de cuotas si es crédito
        $infoCuotas = null;
        if ($venta->esACredito() && $venta->planPago) {
            $cuotas = $venta->planPago->cuotas;
            $infoCuotas = [
                'total' => $cuotas->count(),
                'pagadas' => $cuotas->where('estado_cuota', 'PAGADO')->count(),
                'pendientes' => $cuotas->where('estado_cuota', 'PENDIENTE')->count(),
                'vencidas' => $cuotas->where('estado_cuota', 'VENCIDO')->count(),
                'proxima_vencer' => $cuotas->where('estado_cuota', 'PENDIENTE')
                    ->sortBy('fecha_vencimiento')
                    ->first(),
            ];
        }

        // Estados de pago
        $estadosPago = collect(EstadoPago::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
            'color' => $e->color(),
        ]);

        // Métodos de pago para registrar nuevo pago
        $metodosPago = collect(MetodoPago::cases())->map(fn($e) => [
            'value' => $e->value,
            'label' => $e->label(),
            'requiereReferencia' => $e->requiereReferencia(),
        ]);

        return Inertia::render('Admin/Ventas/Show', [
            'venta' => $venta,
            'infoCuotas' => $infoCuotas,
            'estadosPago' => $estadosPago,
            'metodosPago' => $metodosPago,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venta $venta)
    {
        $venta->load(['cliente', 'vendedor', 'viaje.planViaje.destino']);

        // Solo permitir editar si no hay pagos
        if ($venta->pagos()->exists()) {
            return redirect()->route('ventas.show', $venta->id)
                ->with('error', 'No se puede editar una venta que ya tiene pagos registrados.');
        }

        // Clientes existentes
        $rolCliente = Rol::where('nombre', 'Cliente')->first();
        $clientes = User::where('rol_id', $rolCliente?->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'telefono', 'ci_nit']);

        return Inertia::render('Admin/Ventas/Edit', [
            'venta' => $venta,
            'clientes' => $clientes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVentaRequest $request, Venta $venta)
    {
        // Solo permitir actualizar datos del cliente
        if ($venta->pagos()->exists()) {
            return back()->with('error', 'No se puede modificar una venta con pagos.');
        }

        $venta->update([
            'cliente_id' => $request->cliente_id,
        ]);

        return redirect()->route('ventas.show', $venta->id)
            ->with('success', 'Venta actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage (Cancelar venta).
     */
    public function destroy(Venta $venta)
    {
        // Verificar si tiene pagos
        if ($venta->pagos()->exists()) {
            return back()->with('error', 'No se puede cancelar una venta con pagos registrados. Debe gestionar los reembolsos primero.');
        }

        try {
            DB::beginTransaction();

            // Liberar cupos del viaje
            $viaje = $venta->viaje;
            if ($viaje) {
                // Calcular cantidad de personas (monto_total / precio_base)
                $cantidadPersonas = (int) ($venta->monto_total / $viaje->planViaje->precio_base);
                $viaje->liberarCupos($cantidadPersonas);
            }

            // Eliminar plan de pagos y cuotas si existen
            if ($venta->planPago) {
                $venta->planPago->cuotas()->delete();
                $venta->planPago->delete();
            }

            // Anular la venta
            $venta->update([
                'estado_pago' => EstadoPago::ANULADO,
            ]);

            DB::commit();

            return redirect()->route('ventas.index')
                ->with('success', 'Venta cancelada exitosamente. Los cupos han sido liberados.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cancelar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Registrar un pago para la venta
     */
    public function registrarPago(Request $request, Venta $venta)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01|max:' . $venta->saldoPendiente(),
            'metodo_pago' => 'required|in:' . implode(',', array_column(MetodoPago::cases(), 'value')),
            'referencia' => 'nullable|string|max:100',
            'cuota_id' => 'nullable|exists:cuotas,id',
        ]);

        Pago::create([
            'venta_id' => $venta->id,
            'cuota_id' => $request->cuota_id,
            'fecha_pago' => now(),
            'monto_pagado' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'referencia_comprobante' => $request->referencia,
        ]);

        return back()->with('success', 'Pago registrado exitosamente.');
    }

    /**
     * Generar comprobante/boleto en PDF
     */
    public function generarComprobante(Venta $venta)
    {
        $venta->load([
            'cliente',
            'vendedor',
            'viaje.planViaje.destino',
            'viaje.planViaje.actividadesDiarias' => function ($query) {
                $query->orderBy('dia_numero')->orderBy('hora_inicio');
            },
            'pagos',
        ]);

        $data = [
            'venta' => $venta,
            'fecha_emision' => now()->format('d/m/Y H:i'),
            'numero_comprobante' => 'TT-' . str_pad($venta->id, 6, '0', STR_PAD_LEFT),
        ];

        $pdf = Pdf::loadView('pdf.comprobante-venta', $data);
        
        return $pdf->download('comprobante-' . $data['numero_comprobante'] . '.pdf');
    }

    /**
     * Generar boleto de viaje en PDF
     */
    public function generarBoleto(Venta $venta)
    {
        $venta->load([
            'cliente',
            'viaje.planViaje.destino',
            'viaje.planViaje.actividadesDiarias' => function ($query) {
                $query->orderBy('dia_numero')->orderBy('hora_inicio');
            },
        ]);

        // Solo generar boleto si está pagado
        if (!$venta->estaPagada()) {
            return back()->with('error', 'Solo se puede generar el boleto cuando la venta esté completamente pagada.');
        }

        $data = [
            'venta' => $venta,
            'fecha_emision' => now()->format('d/m/Y H:i'),
            'numero_boleto' => 'BOL-' . str_pad($venta->id, 6, '0', STR_PAD_LEFT),
        ];

        $pdf = Pdf::loadView('pdf.boleto-viaje', $data);
        
        return $pdf->download('boleto-' . $data['numero_boleto'] . '.pdf');
    }
}
