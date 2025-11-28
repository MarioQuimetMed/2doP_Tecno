<?php

namespace Database\Seeders;

use App\Enums\EstadoViaje;
use App\Models\PlanViaje;
use App\Models\Viaje;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ViajeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $planesViaje = PlanViaje::all();

        if ($planesViaje->isEmpty()) {
            $this->command->warn('No hay planes de viaje. Ejecuta primero PlanViajeSeeder.');
            return;
        }

        $hoy = Carbon::now();
        $count = 0;

        foreach ($planesViaje as $planViaje) {
            // Viaje pasado (finalizado)
            $fechaPasada = $hoy->copy()->subDays(rand(30, 60));
            $cuposPasado = rand(15, 30);
            Viaje::create([
                'plan_viaje_id' => $planViaje->id,
                'fecha_salida' => $fechaPasada,
                'fecha_retorno' => $fechaPasada->copy()->addDays($planViaje->duracion_dias - 1),
                'cupos_totales' => $cuposPasado,
                'cupos_disponibles' => 0,
                'estado_viaje' => EstadoViaje::FINALIZADO,
            ]);
            $count++;

            // Viaje próximo (abierto)
            $fechaProxima = $hoy->copy()->addDays(rand(5, 15));
            $cuposProximo = rand(20, 40);
            Viaje::create([
                'plan_viaje_id' => $planViaje->id,
                'fecha_salida' => $fechaProxima,
                'fecha_retorno' => $fechaProxima->copy()->addDays($planViaje->duracion_dias - 1),
                'cupos_totales' => $cuposProximo,
                'cupos_disponibles' => rand(5, $cuposProximo - 5),
                'estado_viaje' => EstadoViaje::ABIERTO,
            ]);
            $count++;

            // Viaje futuro (abierto)
            $fechaFutura = $hoy->copy()->addDays(rand(30, 60));
            $cuposFuturo = rand(25, 50);
            Viaje::create([
                'plan_viaje_id' => $planViaje->id,
                'fecha_salida' => $fechaFutura,
                'fecha_retorno' => $fechaFutura->copy()->addDays($planViaje->duracion_dias - 1),
                'cupos_totales' => $cuposFuturo,
                'cupos_disponibles' => $cuposFuturo,
                'estado_viaje' => EstadoViaje::ABIERTO,
            ]);
            $count++;
        }

        $this->command->info("Se crearon {$count} viajes programados.");
    }
}
