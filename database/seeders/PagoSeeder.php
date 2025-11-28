<?php

namespace Database\Seeders;

use App\Enums\EstadoCuota;
use App\Enums\EstadoPago;
use App\Enums\MetodoPago;
use App\Enums\TipoPago;
use App\Models\Cuota;
use App\Models\Pago;
use App\Models\Venta;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Este seeder crea pagos adicionales para demostrar la funcionalidad
     * del módulo de gestión de pagos.
     */
    public function run(): void
    {
        $this->command->info('Creando pagos de demostración...');

        // Obtener ventas sin pagos o con pagos parciales
        $ventasSinPagar = Venta::where('estado_pago', EstadoPago::PENDIENTE->value)
            ->orWhere('estado_pago', EstadoPago::PARCIAL->value)
            ->with(['planPago.cuotas'])
            ->get();

        if ($ventasSinPagar->isEmpty()) {
            $this->command->warn('No hay ventas pendientes de pago. Ejecuta VentaSeeder primero.');
            return;
        }

        $pagosCreados = 0;
        $metodosPago = MetodoPago::cases();
        $hoy = Carbon::now();

        // Crear pagos variados para las ventas pendientes
        foreach ($ventasSinPagar->take(5) as $venta) {
            // Determinar cuántos pagos crear
            $saldoPendiente = $venta->saldoPendiente();
            
            if ($saldoPendiente <= 0) {
                continue;
            }

            // Si es venta a crédito, pagar algunas cuotas
            if ($venta->esACredito() && $venta->planPago) {
                $cuotasPendientes = $venta->planPago->cuotas()
                    ->where('estado_cuota', '!=', EstadoCuota::PAGADO->value)
                    ->orderBy('numero_cuota')
                    ->get();

                // Pagar entre 1 y 2 cuotas pendientes
                $cuotasAPagar = min(rand(1, 2), $cuotasPendientes->count());
                
                foreach ($cuotasPendientes->take($cuotasAPagar) as $cuota) {
                    $metodo = $metodosPago[array_rand($metodosPago)];
                    $fechaPago = $cuota->fecha_vencimiento->copy()
                        ->subDays(rand(0, 5));
                    
                    // Si la fecha es futura, usar hoy
                    if ($fechaPago->isFuture()) {
                        $fechaPago = $hoy->copy()->subDays(rand(1, 10));
                    }

                    Pago::create([
                        'venta_id' => $venta->id,
                        'cuota_id' => $cuota->id,
                        'monto_pagado' => $cuota->monto_total_cuota,
                        'fecha_pago' => $fechaPago,
                        'metodo_pago' => $metodo->value,
                        'referencia_comprobante' => $this->generarReferencia($metodo),
                    ]);

                    // Actualizar estado de cuota
                    $cuota->update(['estado_cuota' => EstadoCuota::PAGADO]);
                    
                    $pagosCreados++;
                    $this->command->info("Pago de cuota #{$cuota->numero_cuota} - Venta #{$venta->id} - \${$cuota->monto_total_cuota}");
                }

                // Actualizar estado de venta
                $venta->actualizarEstadoPago();
            }
            // Si es venta al contado, hacer un pago parcial o total
            else {
                $metodo = $metodosPago[array_rand($metodosPago)];
                // Pagar entre 50% y 100% del saldo
                $montoPagar = $saldoPendiente * (rand(50, 100) / 100);
                $montoPagar = round($montoPagar, 2);

                Pago::create([
                    'venta_id' => $venta->id,
                    'cuota_id' => null,
                    'monto_pagado' => $montoPagar,
                    'fecha_pago' => $hoy->copy()->subDays(rand(1, 10)),
                    'metodo_pago' => $metodo->value,
                    'referencia_comprobante' => $this->generarReferencia($metodo),
                ]);

                $pagosCreados++;
                $this->command->info("Pago contado - Venta #{$venta->id} - \${$montoPagar}");

                // Actualizar estado de venta
                $venta->actualizarEstadoPago();
            }
        }

        // Crear algunos pagos de hoy para las estadísticas
        $this->crearPagosRecientes($pagosCreados);

        $this->command->info("Total de pagos creados: {$pagosCreados}");
    }

    /**
     * Crear pagos recientes para demostrar estadísticas
     */
    private function crearPagosRecientes(int &$pagosCreados): void
    {
        $hoy = Carbon::now();
        $metodosPago = MetodoPago::cases();

        // Buscar ventas con saldo pendiente
        $ventasConSaldo = Venta::whereIn('estado_pago', [
            EstadoPago::PENDIENTE->value, 
            EstadoPago::PARCIAL->value
        ])->take(3)->get();

        foreach ($ventasConSaldo as $venta) {
            $saldoPendiente = $venta->saldoPendiente();
            if ($saldoPendiente <= 0) continue;

            $metodo = $metodosPago[array_rand($metodosPago)];
            $montoPagar = min(50.00, $saldoPendiente); // Pagos pequeños de demostración

            // Crear pagos en los últimos días
            for ($i = 0; $i < rand(1, 3); $i++) {
                $saldoActual = $venta->fresh()->saldoPendiente();
                if ($saldoActual <= 0) break;

                $montoPagoActual = min($montoPagar, $saldoActual);
                
                Pago::create([
                    'venta_id' => $venta->id,
                    'cuota_id' => null,
                    'monto_pagado' => $montoPagoActual,
                    'fecha_pago' => $hoy->copy()->subDays($i),
                    'metodo_pago' => $metodo->value,
                    'referencia_comprobante' => $this->generarReferencia($metodo),
                ]);

                $pagosCreados++;
                $venta->actualizarEstadoPago();
            }
        }
    }

    /**
     * Generar referencia de comprobante según el método de pago
     */
    private function generarReferencia(MetodoPago $metodo): ?string
    {
        if (!$metodo->requiereReferencia()) {
            return null;
        }

        $prefijos = [
            MetodoPago::TARJETA->value => 'TRJ',
            MetodoPago::QR->value => 'QRP',
            MetodoPago::TRANSFERENCIA->value => 'TRF',
        ];

        $prefijo = $prefijos[$metodo->value] ?? 'PAY';
        return $prefijo . '-' . strtoupper(substr(md5(uniqid()), 0, 8)) . '-' . date('Ymd');
    }
}

