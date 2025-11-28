<?php

namespace Database\Seeders;

use App\Models\Destino;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DestinoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $destinos = [
            // Bolivia
            [
                'pais' => 'Bolivia',
                'ciudad' => 'Potosí',
                'nombre_lugar' => 'Salar de Uyuni',
                'descripcion' => 'El salar más grande del mundo, un espejo natural impresionante que ofrece vistas únicas especialmente durante la época de lluvias. Incluye visitas a la Isla Incahuasi con sus cactus gigantes y los famosos atardeceres reflejados.',
            ],
            [
                'pais' => 'Bolivia',
                'ciudad' => 'La Paz',
                'nombre_lugar' => 'Valle de la Luna',
                'descripcion' => 'Formaciones geológicas únicas que asemejan un paisaje lunar. A solo 10 km del centro de La Paz, ofrece caminatas entre cañones y pináculos de arcilla erosionada.',
            ],
            [
                'pais' => 'Bolivia',
                'ciudad' => 'Rurrenabaque',
                'nombre_lugar' => 'Parque Nacional Madidi',
                'descripcion' => 'Uno de los lugares con mayor biodiversidad del planeta. Tours en la selva amazónica, avistamiento de fauna silvestre y experiencias con comunidades indígenas.',
            ],
            [
                'pais' => 'Bolivia',
                'ciudad' => 'Copacabana',
                'nombre_lugar' => 'Isla del Sol',
                'descripcion' => 'Isla sagrada en el Lago Titicaca, cuna de la civilización Inca según la mitología. Ruinas arqueológicas, senderos panorámicos y cultura ancestral viva.',
            ],
            // Perú
            [
                'pais' => 'Perú',
                'ciudad' => 'Cusco',
                'nombre_lugar' => 'Machu Picchu',
                'descripcion' => 'La ciudadela Inca más famosa del mundo, una de las 7 Maravillas del Mundo Moderno. Arquitectura impresionante, paisajes de montaña y misticismo andino.',
            ],
            [
                'pais' => 'Perú',
                'ciudad' => 'Cusco',
                'nombre_lugar' => 'Valle Sagrado de los Incas',
                'descripcion' => 'Ruta arqueológica que incluye Pisac, Ollantaytambo y Moray. Mercados artesanales, terrazas agrícolas incas y fortalezas de piedra.',
            ],
            [
                'pais' => 'Perú',
                'ciudad' => 'Puno',
                'nombre_lugar' => 'Islas Flotantes de los Uros',
                'descripcion' => 'Islas artificiales construidas con totora en el Lago Titicaca. Cultura viva del pueblo Uros, artesanías y paseos en balsas tradicionales.',
            ],
            // Argentina
            [
                'pais' => 'Argentina',
                'ciudad' => 'Puerto Iguazú',
                'nombre_lugar' => 'Cataratas del Iguazú',
                'descripcion' => 'Sistema de 275 cascadas que forman uno de los espectáculos naturales más impresionantes. Paseos por pasarelas, navegación bajo las cataratas y selva subtropical.',
            ],
            [
                'pais' => 'Argentina',
                'ciudad' => 'Buenos Aires',
                'nombre_lugar' => 'Centro Histórico de Buenos Aires',
                'descripcion' => 'La Boca, San Telmo, Puerto Madero y el Obelisco. Tango, arquitectura europea, gastronomía y vida nocturna inigualable.',
            ],
            [
                'pais' => 'Argentina',
                'ciudad' => 'El Calafate',
                'nombre_lugar' => 'Glaciar Perito Moreno',
                'descripcion' => 'Uno de los pocos glaciares en avance del mundo. Trekking sobre hielo, navegación entre icebergs y el espectáculo del desprendimiento de bloques de hielo.',
            ],
            // Brasil
            [
                'pais' => 'Brasil',
                'ciudad' => 'Río de Janeiro',
                'nombre_lugar' => 'Cristo Redentor y Pan de Azúcar',
                'descripcion' => 'Los íconos más reconocidos de Río. Vistas panorámicas de la ciudad, playas de Copacabana e Ipanema, y la vibrante cultura carioca.',
            ],
            // Chile
            [
                'pais' => 'Chile',
                'ciudad' => 'San Pedro de Atacama',
                'nombre_lugar' => 'Desierto de Atacama',
                'descripcion' => 'El desierto más árido del mundo. Géiseres del Tatio, lagunas altiplánicas, Valle de la Muerte y los cielos más limpios para astronomía.',
            ],
            [
                'pais' => 'Chile',
                'ciudad' => 'Punta Arenas',
                'nombre_lugar' => 'Torres del Paine',
                'descripcion' => 'Parque Nacional con paisajes patagónicos espectaculares. Trekking, glaciares, lagos turquesa y fauna como guanacos y cóndores.',
            ],
            // Colombia
            [
                'pais' => 'Colombia',
                'ciudad' => 'Cartagena',
                'nombre_lugar' => 'Ciudad Amurallada de Cartagena',
                'descripcion' => 'Centro histórico colonial declarado Patrimonio de la Humanidad. Calles empedradas, balcones floridos, playas del Caribe e islas del Rosario.',
            ],
            // Ecuador
            [
                'pais' => 'Ecuador',
                'ciudad' => 'Puerto Ayora',
                'nombre_lugar' => 'Islas Galápagos',
                'descripcion' => 'Archipiélago único donde Charles Darwin desarrolló su teoría de la evolución. Tortugas gigantes, iguanas marinas, lobos marinos y buceo de clase mundial.',
            ],
        ];

        foreach ($destinos as $destino) {
            Destino::create($destino);
        }
    }
}
