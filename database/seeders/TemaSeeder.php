<?php

namespace Database\Seeders;

use App\Models\Tema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemaSeeder extends Seeder
{
    /**
     * Ejecutar el seeder de temas.
     */
    public function run(): void
    {
        $temas = [
            [
                'nombre' => 'Niños',
                'descripcion' => 'Tema colorido y divertido con colores vibrantes y fuentes grandes, ideal para los más pequeños',
                'tipo' => 'ninos',
                'css_variables' => [
                    'color_primary' => '#ec4899',
                    'color_primary_hover' => '#db2777',
                    'color_primary_light' => '#f472b6',
                    'color_secondary' => '#8b5cf6',
                    'color_accent' => '#f59e0b',
                    'bg_primary' => '#fefce8',
                    'bg_secondary' => '#fef9c3',
                    'bg_tertiary' => '#fef08a',
                    'bg_nav' => 'linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%)',
                    'bg_card' => '#ffffff',
                    'text_primary' => '#1e1b4b',
                    'text_secondary' => '#4c1d95',
                    'text_muted' => '#7c3aed',
                    'border_color' => '#f0abfc',
                    'font_family' => 'Comic Sans MS, cursive, sans-serif',
                    'font_size_multiplier' => 1.1,
                    'border_radius' => '1rem',
                ],
                'activo' => true,
            ],
            [
                'nombre' => 'Jóvenes',
                'descripcion' => 'Tema moderno con gradientes y efectos de brillo, diseño atractivo para la generación digital',
                'tipo' => 'jovenes',
                'css_variables' => [
                    'color_primary' => '#7c3aed',
                    'color_primary_hover' => '#6d28d9',
                    'color_primary_light' => '#a78bfa',
                    'color_secondary' => '#06b6d4',
                    'color_accent' => '#f43f5e',
                    'bg_primary' => '#0f172a',
                    'bg_secondary' => '#1e293b',
                    'bg_tertiary' => '#334155',
                    'bg_nav' => 'linear-gradient(135deg, #7c3aed 0%, #06b6d4 100%)',
                    'bg_card' => '#1e293b',
                    'text_primary' => '#f1f5f9',
                    'text_secondary' => '#cbd5e1',
                    'text_muted' => '#94a3b8',
                    'border_color' => '#475569',
                    'font_family' => 'Inter, system-ui, sans-serif',
                    'font_size_multiplier' => 1,
                    'border_radius' => '0.75rem',
                    'has_glow_effects' => true,
                ],
                'activo' => true,
            ],
            [
                'nombre' => 'Adultos',
                'descripcion' => 'Tema profesional y minimalista, elegante y fácil de leer para uso empresarial',
                'tipo' => 'adultos',
                'css_variables' => [
                    'color_primary' => '#2563eb',
                    'color_primary_hover' => '#1d4ed8',
                    'color_primary_light' => '#60a5fa',
                    'color_secondary' => '#64748b',
                    'color_accent' => '#059669',
                    'bg_primary' => '#ffffff',
                    'bg_secondary' => '#f8fafc',
                    'bg_tertiary' => '#f1f5f9',
                    'bg_nav' => '#1e293b',
                    'bg_card' => '#ffffff',
                    'text_primary' => '#1e293b',
                    'text_secondary' => '#475569',
                    'text_muted' => '#94a3b8',
                    'border_color' => '#e2e8f0',
                    'font_family' => 'Figtree, Segoe UI, system-ui, sans-serif',
                    'font_size_multiplier' => 1,
                    'border_radius' => '0.25rem',
                ],
                'activo' => true,
            ],
        ];

        foreach ($temas as $tema) {
            Tema::updateOrCreate(
                ['tipo' => $tema['tipo']],
                $tema
            );
        }

        $this->command->info('✅ Temas creados: Niños, Jóvenes, Adultos');
    }
}
