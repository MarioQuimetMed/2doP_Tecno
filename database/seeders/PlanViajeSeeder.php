<?php

namespace Database\Seeders;

use App\Models\ActividadDiaria;
use App\Models\Destino;
use App\Models\PlanViaje;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanViajeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener destinos existentes
        $destinos = Destino::all();

        if ($destinos->isEmpty()) {
            $this->command->warn('No hay destinos disponibles. Ejecuta primero DestinoSeeder.');
            return;
        }

        // Planes de viaje de ejemplo
        $planesViaje = [
            // Bolivia - Salar de Uyuni
            [
                'destino_query' => ['pais' => 'Bolivia', 'nombre_lugar' => 'Salar de Uyuni'],
                'nombre' => 'Aventura en el Salar - 3 Días',
                'descripcion' => 'Una experiencia única en el desierto de sal más grande del mundo. Incluye visitas a la Isla Incahuasi, atardecer en el salar y observación de estrellas.',
                'duracion_dias' => 3,
                'precio_base' => 450.00,
                'actividades' => [
                    ['dia' => 1, 'hora' => '06:00', 'desc' => 'Salida desde Uyuni hacia el salar'],
                    ['dia' => 1, 'hora' => '08:30', 'desc' => 'Visita al cementerio de trenes'],
                    ['dia' => 1, 'hora' => '10:00', 'desc' => 'Ingreso al Salar de Uyuni - sesión fotográfica con efectos de perspectiva'],
                    ['dia' => 1, 'hora' => '12:30', 'desc' => 'Almuerzo en el salar'],
                    ['dia' => 1, 'hora' => '15:00', 'desc' => 'Visita a la Isla Incahuasi y sus cactus gigantes'],
                    ['dia' => 1, 'hora' => '18:00', 'desc' => 'Atardecer en el salar - fotografía'],
                    ['dia' => 2, 'hora' => '05:30', 'desc' => 'Amanecer en el salar'],
                    ['dia' => 2, 'hora' => '08:00', 'desc' => 'Desayuno y traslado a las lagunas'],
                    ['dia' => 2, 'hora' => '11:00', 'desc' => 'Visita a Laguna Colorada'],
                    ['dia' => 2, 'hora' => '14:00', 'desc' => 'Almuerzo campestre'],
                    ['dia' => 2, 'hora' => '16:00', 'desc' => 'Géiseres Sol de Mañana'],
                    ['dia' => 2, 'hora' => '18:30', 'desc' => 'Aguas termales de Polques'],
                    ['dia' => 3, 'hora' => '07:00', 'desc' => 'Desayuno en el refugio'],
                    ['dia' => 3, 'hora' => '09:00', 'desc' => 'Visita a Laguna Verde'],
                    ['dia' => 3, 'hora' => '12:00', 'desc' => 'Regreso a Uyuni'],
                    ['dia' => 3, 'hora' => '17:00', 'desc' => 'Llegada a Uyuni - fin del tour'],
                ],
            ],
            // Bolivia - Lago Titicaca
            [
                'destino_query' => ['pais' => 'Bolivia', 'nombre_lugar' => 'Lago Titicaca'],
                'nombre' => 'Misterios del Titicaca - 2 Días',
                'descripcion' => 'Descubre la cuna de la civilización Inca navegando por el lago navegable más alto del mundo. Visita la Isla del Sol y conoce las leyendas andinas.',
                'duracion_dias' => 2,
                'precio_base' => 280.00,
                'actividades' => [
                    ['dia' => 1, 'hora' => '07:00', 'desc' => 'Traslado Copacabana - Embarcadero'],
                    ['dia' => 1, 'hora' => '08:30', 'desc' => 'Navegación hacia la Isla del Sol'],
                    ['dia' => 1, 'hora' => '10:30', 'desc' => 'Llegada y caminata por senderos incas'],
                    ['dia' => 1, 'hora' => '12:30', 'desc' => 'Almuerzo típico con vista al lago'],
                    ['dia' => 1, 'hora' => '14:30', 'desc' => 'Visita a la Escalinata del Inca y Fuente Sagrada'],
                    ['dia' => 1, 'hora' => '17:00', 'desc' => 'Atardecer y ritual de agradecimiento a la Pachamama'],
                    ['dia' => 1, 'hora' => '19:00', 'desc' => 'Cena y hospedaje en la isla'],
                    ['dia' => 2, 'hora' => '06:00', 'desc' => 'Amanecer sobre el lago'],
                    ['dia' => 2, 'hora' => '08:00', 'desc' => 'Desayuno tradicional'],
                    ['dia' => 2, 'hora' => '10:00', 'desc' => 'Visita a la Isla de la Luna'],
                    ['dia' => 2, 'hora' => '13:00', 'desc' => 'Almuerzo y tiempo libre'],
                    ['dia' => 2, 'hora' => '15:00', 'desc' => 'Regreso a Copacabana'],
                    ['dia' => 2, 'hora' => '17:00', 'desc' => 'Visita a la Basílica de Copacabana'],
                ],
            ],
            // Perú - Machu Picchu
            [
                'destino_query' => ['pais' => 'Perú', 'nombre_lugar' => 'Machu Picchu'],
                'nombre' => 'Machu Picchu Express - 2 Días',
                'descripcion' => 'Viaje exprés a la maravilla del mundo. Incluye tren panorámico, guía especializado y entrada a la ciudadela.',
                'duracion_dias' => 2,
                'precio_base' => 650.00,
                'actividades' => [
                    ['dia' => 1, 'hora' => '05:00', 'desc' => 'Salida en tren Vistadome desde Cusco'],
                    ['dia' => 1, 'hora' => '09:00', 'desc' => 'Llegada a Aguas Calientes'],
                    ['dia' => 1, 'hora' => '10:00', 'desc' => 'Check-in en hotel y descanso'],
                    ['dia' => 1, 'hora' => '12:00', 'desc' => 'Almuerzo en restaurante local'],
                    ['dia' => 1, 'hora' => '14:00', 'desc' => 'Paseo por Aguas Calientes'],
                    ['dia' => 1, 'hora' => '16:00', 'desc' => 'Baños termales (opcional)'],
                    ['dia' => 1, 'hora' => '19:00', 'desc' => 'Cena y briefing del tour'],
                    ['dia' => 2, 'hora' => '05:30', 'desc' => 'Bus a Machu Picchu para amanecer'],
                    ['dia' => 2, 'hora' => '06:30', 'desc' => 'Entrada a la ciudadela'],
                    ['dia' => 2, 'hora' => '07:00', 'desc' => 'Tour guiado por Machu Picchu (3 horas)'],
                    ['dia' => 2, 'hora' => '10:00', 'desc' => 'Tiempo libre para explorar'],
                    ['dia' => 2, 'hora' => '12:00', 'desc' => 'Descenso y almuerzo'],
                    ['dia' => 2, 'hora' => '15:00', 'desc' => 'Tren de regreso a Cusco'],
                    ['dia' => 2, 'hora' => '19:00', 'desc' => 'Llegada a Cusco'],
                ],
            ],
            // Argentina - Perito Moreno
            [
                'destino_query' => ['pais' => 'Argentina', 'nombre_lugar' => 'Glaciar Perito Moreno'],
                'nombre' => 'Glaciares de la Patagonia - 4 Días',
                'descripcion' => 'Aventura en la Patagonia Argentina. Contempla los impresionantes glaciares, realiza trekking sobre hielo y navega entre témpanos.',
                'duracion_dias' => 4,
                'precio_base' => 980.00,
                'actividades' => [
                    ['dia' => 1, 'hora' => '10:00', 'desc' => 'Llegada a El Calafate - traslado al hotel'],
                    ['dia' => 1, 'hora' => '12:00', 'desc' => 'Almuerzo y tiempo libre'],
                    ['dia' => 1, 'hora' => '15:00', 'desc' => 'Visita a la Reserva Laguna Nimez'],
                    ['dia' => 1, 'hora' => '19:00', 'desc' => 'Cena típica patagónica con cordero'],
                    ['dia' => 2, 'hora' => '08:00', 'desc' => 'Salida al Parque Nacional Los Glaciares'],
                    ['dia' => 2, 'hora' => '10:00', 'desc' => 'Llegada al Glaciar Perito Moreno - pasarelas'],
                    ['dia' => 2, 'hora' => '12:30', 'desc' => 'Almuerzo con vista al glaciar'],
                    ['dia' => 2, 'hora' => '14:00', 'desc' => 'Navegación Safari Náutico'],
                    ['dia' => 2, 'hora' => '17:00', 'desc' => 'Regreso a El Calafate'],
                    ['dia' => 3, 'hora' => '07:30', 'desc' => 'Excursión Minitrekking sobre el glaciar'],
                    ['dia' => 3, 'hora' => '10:00', 'desc' => 'Caminata sobre el hielo (2 horas)'],
                    ['dia' => 3, 'hora' => '12:30', 'desc' => 'Brindis con whisky y hielo milenario'],
                    ['dia' => 3, 'hora' => '14:00', 'desc' => 'Almuerzo campestre'],
                    ['dia' => 3, 'hora' => '17:00', 'desc' => 'Regreso al hotel'],
                    ['dia' => 4, 'hora' => '09:00', 'desc' => 'Navegación Todo Glaciares (opcional)'],
                    ['dia' => 4, 'hora' => '14:00', 'desc' => 'Almuerzo de despedida'],
                    ['dia' => 4, 'hora' => '16:00', 'desc' => 'Traslado al aeropuerto'],
                ],
            ],
            // Brasil - Cataratas del Iguazú
            [
                'destino_query' => ['pais' => 'Brasil', 'nombre_lugar' => 'Cataratas del Iguazú'],
                'nombre' => 'Cataratas del Iguazú - Full Day',
                'descripcion' => 'Descubre las impresionantes Cataratas del Iguazú desde el lado brasileño. Incluye paseo en zodiac bajo las cascadas.',
                'duracion_dias' => 1,
                'precio_base' => 180.00,
                'actividades' => [
                    ['dia' => 1, 'hora' => '08:00', 'desc' => 'Recojo del hotel en Foz de Iguazú'],
                    ['dia' => 1, 'hora' => '09:00', 'desc' => 'Ingreso al Parque Nacional'],
                    ['dia' => 1, 'hora' => '09:30', 'desc' => 'Recorrido por las pasarelas panorámicas'],
                    ['dia' => 1, 'hora' => '11:00', 'desc' => 'Garganta del Diablo - mirador principal'],
                    ['dia' => 1, 'hora' => '12:30', 'desc' => 'Almuerzo buffet en Porto Canoas'],
                    ['dia' => 1, 'hora' => '14:00', 'desc' => 'Paseo en Macuco Safari - zodiac bajo las cataratas'],
                    ['dia' => 1, 'hora' => '16:00', 'desc' => 'Tiempo libre y compras en el parque'],
                    ['dia' => 1, 'hora' => '17:30', 'desc' => 'Regreso al hotel'],
                ],
            ],
            // Bolivia - Yungas
            [
                'destino_query' => ['pais' => 'Bolivia', 'nombre_lugar' => 'Camino de los Yungas'],
                'nombre' => 'Death Road Extreme - 1 Día',
                'descripcion' => 'Aventura en bicicleta por el famoso Camino de la Muerte. Para amantes de la adrenalina con equipamiento de seguridad completo.',
                'duracion_dias' => 1,
                'precio_base' => 120.00,
                'actividades' => [
                    ['dia' => 1, 'hora' => '07:00', 'desc' => 'Recojo del hotel en La Paz'],
                    ['dia' => 1, 'hora' => '08:30', 'desc' => 'Llegada a La Cumbre (4,700 msnm) - Briefing de seguridad'],
                    ['dia' => 1, 'hora' => '09:00', 'desc' => 'Entrega de equipos y prueba de bicicletas'],
                    ['dia' => 1, 'hora' => '09:30', 'desc' => 'Inicio del descenso - tramo asfaltado'],
                    ['dia' => 1, 'hora' => '11:00', 'desc' => 'Llegada a Unduavi - refrigerio'],
                    ['dia' => 1, 'hora' => '11:30', 'desc' => 'Inicio del Camino de la Muerte - tramo de ripio'],
                    ['dia' => 1, 'hora' => '14:30', 'desc' => 'Llegada a Yolosa - almuerzo celebratorio'],
                    ['dia' => 1, 'hora' => '16:00', 'desc' => 'Piscina y duchas'],
                    ['dia' => 1, 'hora' => '17:00', 'desc' => 'Entrega de certificados'],
                    ['dia' => 1, 'hora' => '17:30', 'desc' => 'Regreso a La Paz'],
                ],
            ],
            // Chile - Torres del Paine
            [
                'destino_query' => ['pais' => 'Chile', 'nombre_lugar' => 'Torres del Paine'],
                'nombre' => 'Torres del Paine - 5 Días Trekking',
                'descripcion' => 'Circuito W completo en Torres del Paine. Trekking por los senderos más espectaculares de la Patagonia chilena.',
                'duracion_dias' => 5,
                'precio_base' => 1250.00,
                'actividades' => [
                    ['dia' => 1, 'hora' => '08:00', 'desc' => 'Traslado Puerto Natales - Torres del Paine'],
                    ['dia' => 1, 'hora' => '11:00', 'desc' => 'Inicio trek hacia Campamento Torres'],
                    ['dia' => 1, 'hora' => '14:00', 'desc' => 'Almuerzo en el sendero'],
                    ['dia' => 1, 'hora' => '18:00', 'desc' => 'Llegada al campamento - cena'],
                    ['dia' => 2, 'hora' => '05:00', 'desc' => 'Ascenso a Base Torres para amanecer'],
                    ['dia' => 2, 'hora' => '07:00', 'desc' => 'Vista Torres del Paine - fotografía'],
                    ['dia' => 2, 'hora' => '10:00', 'desc' => 'Descenso y desayuno'],
                    ['dia' => 2, 'hora' => '13:00', 'desc' => 'Trek hacia Los Cuernos'],
                    ['dia' => 2, 'hora' => '18:00', 'desc' => 'Campamento Los Cuernos'],
                    ['dia' => 3, 'hora' => '08:00', 'desc' => 'Desayuno y preparación'],
                    ['dia' => 3, 'hora' => '09:00', 'desc' => 'Trek Valle del Francés'],
                    ['dia' => 3, 'hora' => '12:00', 'desc' => 'Mirador Británico'],
                    ['dia' => 3, 'hora' => '15:00', 'desc' => 'Trek hacia Paine Grande'],
                    ['dia' => 3, 'hora' => '18:00', 'desc' => 'Refugio Paine Grande'],
                    ['dia' => 4, 'hora' => '08:00', 'desc' => 'Trek hacia Glaciar Grey'],
                    ['dia' => 4, 'hora' => '12:00', 'desc' => 'Vista del Glaciar Grey'],
                    ['dia' => 4, 'hora' => '14:00', 'desc' => 'Almuerzo junto al glaciar'],
                    ['dia' => 4, 'hora' => '16:00', 'desc' => 'Regreso a Paine Grande'],
                    ['dia' => 5, 'hora' => '08:00', 'desc' => 'Navegación catamarán por Lago Pehoé'],
                    ['dia' => 5, 'hora' => '10:00', 'desc' => 'Pudeto y traslado'],
                    ['dia' => 5, 'hora' => '14:00', 'desc' => 'Llegada a Puerto Natales - almuerzo de despedida'],
                ],
            ],
            // Colombia - Cartagena
            [
                'destino_query' => ['pais' => 'Colombia', 'nombre_lugar' => 'Cartagena de Indias'],
                'nombre' => 'Cartagena Colonial - 3 Días',
                'descripcion' => 'Descubre la magia del Caribe colombiano. Historia, playas paradisíacas y gastronomía en la ciudad amurallada.',
                'duracion_dias' => 3,
                'precio_base' => 520.00,
                'actividades' => [
                    ['dia' => 1, 'hora' => '10:00', 'desc' => 'Llegada y traslado al hotel en Ciudad Amurallada'],
                    ['dia' => 1, 'hora' => '12:00', 'desc' => 'Almuerzo en restaurante local'],
                    ['dia' => 1, 'hora' => '15:00', 'desc' => 'Tour a pie por la Ciudad Amurallada'],
                    ['dia' => 1, 'hora' => '17:00', 'desc' => 'Visita Castillo San Felipe de Barajas'],
                    ['dia' => 1, 'hora' => '19:00', 'desc' => 'Atardecer en Café del Mar'],
                    ['dia' => 1, 'hora' => '20:30', 'desc' => 'Cena y rumba en Getsemaní'],
                    ['dia' => 2, 'hora' => '08:00', 'desc' => 'Desayuno y traslado a Playa Blanca'],
                    ['dia' => 2, 'hora' => '10:00', 'desc' => 'Llegada a Islas del Rosario'],
                    ['dia' => 2, 'hora' => '11:00', 'desc' => 'Snorkel en arrecifes de coral'],
                    ['dia' => 2, 'hora' => '13:00', 'desc' => 'Almuerzo caribeño con mariscos'],
                    ['dia' => 2, 'hora' => '15:00', 'desc' => 'Playa Blanca - tiempo libre'],
                    ['dia' => 2, 'hora' => '17:00', 'desc' => 'Regreso a Cartagena'],
                    ['dia' => 3, 'hora' => '09:00', 'desc' => 'Tour Volcán del Totumo - baño de lodo'],
                    ['dia' => 3, 'hora' => '13:00', 'desc' => 'Almuerzo y regreso a Cartagena'],
                    ['dia' => 3, 'hora' => '15:00', 'desc' => 'Compras en Las Bóvedas'],
                    ['dia' => 3, 'hora' => '17:00', 'desc' => 'Traslado al aeropuerto'],
                ],
            ],
        ];

        foreach ($planesViaje as $planData) {
            // Buscar destino
            $destino = Destino::where('pais', $planData['destino_query']['pais'])
                ->where('nombre_lugar', 'ILIKE', '%' . $planData['destino_query']['nombre_lugar'] . '%')
                ->first();

            if (!$destino) {
                $this->command->warn("Destino no encontrado: {$planData['destino_query']['nombre_lugar']} en {$planData['destino_query']['pais']}");
                continue;
            }

            // Crear plan de viaje
            $plan = PlanViaje::create([
                'destino_id' => $destino->id,
                'nombre' => $planData['nombre'],
                'descripcion' => $planData['descripcion'],
                'duracion_dias' => $planData['duracion_dias'],
                'precio_base' => $planData['precio_base'],
            ]);

            // Crear actividades
            foreach ($planData['actividades'] as $actividadData) {
                ActividadDiaria::create([
                    'plan_viaje_id' => $plan->id,
                    'dia_numero' => $actividadData['dia'],
                    'descripcion_actividad' => $actividadData['desc'],
                    'hora_inicio' => $actividadData['hora'],
                ]);
            }

            $this->command->info("Plan creado: {$plan->nombre} con " . count($planData['actividades']) . " actividades");
        }

        $this->command->info('Seeder de Planes de Viaje completado.');
    }
}
