<?php

namespace Database\Seeders;

use App\Enums\EstadoCuota;
use App\Enums\EstadoPago;
use App\Enums\EstadoViaje;
use App\Enums\MetodoPago;
use App\Enums\TipoPago;
use App\Models\Pago;
use App\Models\PlanPago;
use App\Models\Rol;
use App\Models\User;
use App\Models\Venta;
use App\Models\Viaje;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener roles
        $clienteRol = Rol::where('nombre', 'Cliente')->first();
        $vendedorRol = Rol::where('nombre', 'Vendedor')->first();

        if (!$clienteRol || !$vendedorRol) {
            $this->command->warn('Faltan roles. Ejecuta RolSeeder primero.');
            return;
        }

        // Crear clientes adicionales si no hay suficientes
        $clientes = User::where('rol_id', $clienteRol->id)->get();
        if ($clientes->count() < 5) {
            $nuevosClientes = [
                ['name' => 'Carlos Mendoza', 'email' => 'carlos.mendoza@email.com', 'telefono' => '71234567', 'ci_nit' => '4567890'],
                ['name' => 'Ana García', 'email' => 'ana.garcia@email.com', 'telefono' => '72345678', 'ci_nit' => '5678901'],
                ['name' => 'Roberto Flores', 'email' => 'roberto.flores@email.com', 'telefono' => '73456789', 'ci_nit' => '6789012'],
                ['name' => 'Lucía Vargas', 'email' => 'lucia.vargas@email.com', 'telefono' => '74567890', 'ci_nit' => '7890123'],
                ['name' => 'Miguel Ríos', 'email' => 'miguel.rios@email.com', 'telefono' => '75678901', 'ci_nit' => '8901234'],
                ['name' => 'Patricia Soto', 'email' => 'patricia.soto@email.com', 'telefono' => '76789012', 'ci_nit' => '9012345'],
                ['name' => 'Fernando Cruz', 'email' => 'fernando.cruz@email.com', 'telefono' => '77890123', 'ci_nit' => '0123456'],
                ['name' => 'Carmen Luna', 'email' => 'carmen.luna@email.com', 'telefono' => '78901234', 'ci_nit' => '1234560'],
            ];

            foreach ($nuevosClientes as $clienteData) {
                User::create([
                    'rol_id' => $clienteRol->id,
                    'name' => $clienteData['name'],
                    'email' => $clienteData['email'],
                    'password' => bcrypt('password'),
                    'telefono' => $clienteData['telefono'],
                    'ci_nit' => $clienteData['ci_nit'],
                    'email_verified_at' => now(),
                ]);
            }
            $clientes = User::where('rol_id', $clienteRol->id)->get();
        }

        // Obtener vendedores
        $vendedores = User::where('rol_id', $vendedorRol->id)->get();
        if ($vendedores->isEmpty()) {
            $this->command->warn('No hay vendedores. Usando propietario como vendedor.');
            $vendedores = User::whereHas('rol', fn($q) => $q->where('nombre', 'Propietario'))->get();
        }

        // Obtener viajes disponibles (usando columnas correctas: estado_viaje, cupos_disponibles)
        $viajesDisponibles = Viaje::where('estado_viaje', EstadoViaje::ABIERTO->value)
            ->where('cupos_disponibles', '>', 0)
            ->with('planViaje')
            ->get();

        if ($viajesDisponibles->isEmpty()) {
            $this->command->warn('No hay viajes disponibles. Ejecuta ViajeSeeder primero.');
            return;
        }

        $hoy = Carbon::now();
        $ventasCreadas = 0;

        // Crear ventas variadas
        foreach ($viajesDisponibles->take(6) as $index => $viaje) {
            $cliente = $clientes->random();
            $vendedor = $vendedores->random();
            $cantidadPersonas = rand(1, min(3, $viaje->cupos_disponibles));
            
            // Usar precio del plan de viaje
            $precioUnitario = $viaje->planViaje->precio_base ?? 500;
            $montoTotal = $precioUnitario * $cantidadPersonas;

            // Alternar entre contado y crédito
            $esCredito = $index % 2 === 0;

            // Venta al CONTADO (pagada completamente)
            if (!$esCredito) {
                $venta = Venta::create([
                    'cliente_id' => $cliente->id,
                    'vendedor_id' => $vendedor->id,
                    'viaje_id' => $viaje->id,
                    'fecha_venta' => $hoy->copy()->subDays(rand(1, 15)),
                    'monto_total' => $montoTotal,
                    'tipo_pago' => TipoPago::CONTADO,
                    'estado_pago' => EstadoPago::COMPLETADO,
                ]);

                // Crear pago único
                Pago::create([
                    'venta_id' => $venta->id,
                    'cuota_id' => null,
                    'monto_pagado' => $montoTotal,
                    'fecha_pago' => $venta->fecha_venta,
                    'metodo_pago' => collect(MetodoPago::cases())->random()->value,
                    'referencia_comprobante' => 'TRX-' . strtoupper(uniqid()),
                ]);

                // Reservar cupos (usando columna correcta)
                $viaje->decrement('cupos_disponibles', $cantidadPersonas);

                $this->command->info("Venta CONTADO #{$venta->id} - {$cliente->name} - \${$montoTotal}");
                $ventasCreadas++;
            }
            // Venta a CRÉDITO
            else {
                $cantidadCuotas = collect([3, 6, 12])->random();
                $tasaInteres = match($cantidadCuotas) {
                    3 => 5,
                    6 => 8,
                    12 => 12,
                    default => 5,
                };

                // Calcular monto con interés compuesto
                $tasaMensual = $tasaInteres / 100;
                $montoConInteres = $montoTotal;
                if ($tasaMensual > 0) {
                    $cuotaMensual = $montoTotal * (
                        ($tasaMensual * pow(1 + $tasaMensual, $cantidadCuotas)) /
                        (pow(1 + $tasaMensual, $cantidadCuotas) - 1)
                    );
                    $montoConInteres = $cuotaMensual * $cantidadCuotas;
                }

                // Determinar estado de pago (algunas con pagos parciales, otras pendientes)
                $estadoPago = $index < 2 ? EstadoPago::PARCIAL : EstadoPago::PENDIENTE;

                $fechaVenta = $hoy->copy()->subDays(rand(30, 60));
                
                $venta = Venta::create([
                    'cliente_id' => $cliente->id,
                    'vendedor_id' => $vendedor->id,
                    'viaje_id' => $viaje->id,
                    'fecha_venta' => $fechaVenta,
                    'monto_total' => round($montoConInteres, 2),
                    'tipo_pago' => TipoPago::CREDITO,
                    'estado_pago' => $estadoPago,
                ]);

                // Crear plan de pago
                $planPago = PlanPago::create([
                    'venta_id' => $venta->id,
                    'cantidad_cuotas' => $cantidadCuotas,
                    'tasa_interes' => $tasaInteres,
                    'dia_vencimiento_mensual' => rand(1, 28),
                ]);

                // Generar cuotas usando el método del modelo
                $planPago->generarCuotas();

                // Si es pago parcial, crear algunos pagos
                if ($estadoPago === EstadoPago::PARCIAL) {
                    $cuotasAPagar = rand(1, min(2, $cantidadCuotas - 1));
                    $cuotas = $planPago->cuotas()->orderBy('numero_cuota')->take($cuotasAPagar)->get();
                    
                    foreach ($cuotas as $cuota) {
                        Pago::create([
                            'venta_id' => $venta->id,
                            'cuota_id' => $cuota->id,
                            'monto_pagado' => $cuota->monto_total_cuota,
                            'fecha_pago' => $cuota->fecha_vencimiento->copy()->subDays(rand(1, 5)),
                            'metodo_pago' => collect(MetodoPago::cases())->random()->value,
                            'referencia_comprobante' => 'TRX-' . strtoupper(uniqid()),
                        ]);
                        
                        // Marcar cuota como pagada
                        $cuota->update(['estado_cuota' => EstadoCuota::PAGADO]);
                    }
                }

                // Reservar cupos (usando columna correcta)
                $viaje->decrement('cupos_disponibles', $cantidadPersonas);

                $this->command->info("Venta CREDITO #{$venta->id} - {$cliente->name} - {$cantidadCuotas} cuotas - \${$montoConInteres}");
                $ventasCreadas++;
            }
        }

        $this->command->info("Se crearon {$ventasCreadas} ventas de prueba.");
    }
}
