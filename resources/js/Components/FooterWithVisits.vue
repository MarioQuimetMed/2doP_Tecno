<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { EyeIcon, UsersIcon, ClockIcon } from '@heroicons/vue/24/outline';

const page = usePage();

// Props globales de visitas desde Inertia
const visitasData = computed(() => page.props.visitas || {
    total: 0,
    hoy: 0,
    unicas: 0
});

// Formatear números grandes
const formatNumber = (num) => {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toLocaleString('es-BO');
};
</script>

<template>
    <footer 
        class="border-t py-6 mt-8 theme-transition"
        style="background: var(--bg-secondary); border-color: var(--border-color);"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <!-- Info de la empresa -->
                <div class="text-center md:text-left">
                    <p 
                        class="text-sm font-medium"
                        style="color: var(--text-primary);"
                    >
                        🌴 TendenciaTours SRL
                    </p>
                    <p 
                        class="text-xs mt-1"
                        style="color: var(--text-muted);"
                    >
                        © {{ new Date().getFullYear() }} - Todos los derechos reservados
                    </p>
                </div>

                <!-- Contador de visitas -->
                <div 
                    class="flex items-center gap-6 px-4 py-2 rounded-lg"
                    style="background: var(--bg-card); border: 1px solid var(--border-color);"
                >
                    <!-- Visitas totales -->
                    <div class="flex items-center gap-2" title="Visitas totales">
                        <EyeIcon class="w-5 h-5" style="color: var(--accent-primary);" />
                        <div class="text-center">
                            <p 
                                class="text-lg font-bold leading-none"
                                style="color: var(--text-primary);"
                            >
                                {{ formatNumber(visitasData.total) }}
                            </p>
                            <p 
                                class="text-xs"
                                style="color: var(--text-muted);"
                            >
                                Total
                            </p>
                        </div>
                    </div>

                    <!-- Separador -->
                    <div 
                        class="h-8 w-px"
                        style="background: var(--border-color);"
                    ></div>

                    <!-- Visitas de hoy -->
                    <div class="flex items-center gap-2" title="Visitas de hoy">
                        <ClockIcon class="w-5 h-5" style="color: var(--accent-secondary, var(--accent-primary));" />
                        <div class="text-center">
                            <p 
                                class="text-lg font-bold leading-none"
                                style="color: var(--text-primary);"
                            >
                                {{ formatNumber(visitasData.hoy) }}
                            </p>
                            <p 
                                class="text-xs"
                                style="color: var(--text-muted);"
                            >
                                Hoy
                            </p>
                        </div>
                    </div>

                    <!-- Separador -->
                    <div 
                        class="h-8 w-px"
                        style="background: var(--border-color);"
                    ></div>

                    <!-- Visitantes únicos -->
                    <div class="flex items-center gap-2" title="Visitantes únicos">
                        <UsersIcon class="w-5 h-5" style="color: var(--accent-tertiary, var(--accent-primary));" />
                        <div class="text-center">
                            <p 
                                class="text-lg font-bold leading-none"
                                style="color: var(--text-primary);"
                            >
                                {{ formatNumber(visitasData.unicas) }}
                            </p>
                            <p 
                                class="text-xs"
                                style="color: var(--text-muted);"
                            >
                                Únicos
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Info adicional -->
                <div class="text-center md:text-right">
                    <p 
                        class="text-xs"
                        style="color: var(--text-muted);"
                    >
                        Desarrollado con ❤️ en Bolivia
                    </p>
                </div>
            </div>
        </div>
    </footer>
</template>
