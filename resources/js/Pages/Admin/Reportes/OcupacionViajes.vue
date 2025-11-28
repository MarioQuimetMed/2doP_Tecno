<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import {
    TruckIcon,
    ArrowDownTrayIcon,
    FunnelIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    viajes: Array,
    estadisticas: Object,
    estados: Object,
    filtros: Object,
});

const estado = ref(props.filtros.estado || "");
const fechaInicio = ref(props.filtros.fecha_inicio || "");
const fechaFin = ref(props.filtros.fecha_fin || "");

const aplicarFiltros = () => {
    router.get(
        "/reportes/ocupacion-viajes",
        {
            estado: estado.value || undefined,
            fecha_inicio: fechaInicio.value || undefined,
            fecha_fin: fechaFin.value || undefined,
        },
        { preserveState: true }
    );
};

const getOcupacionColor = (porcentaje) => {
    if (porcentaje >= 90) return "bg-emerald-500";
    if (porcentaje >= 70) return "bg-blue-500";
    if (porcentaje >= 50) return "bg-amber-500";
    if (porcentaje >= 25) return "bg-orange-500";
    return "bg-red-500";
};

const getEstadoColor = (estado) => {
    const colores = {
        ABIERTO: "bg-emerald-100 text-emerald-800",
        LLENO: "bg-blue-100 text-blue-800",
        EN_CURSO: "bg-purple-100 text-purple-800",
        FINALIZADO: "bg-gray-100 text-gray-800",
        CANCELADO: "bg-red-100 text-red-800",
    };
    return colores[estado] || "bg-gray-100 text-gray-800";
};
</script>

<template>
    <Head title="Ocupación de Viajes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Reporte de Ocupación de Viajes
                </h2>
                <a
                    :href="
                        '/reportes/exportar-ocupacion-excel?estado=' +
                        (filtros.estado || '') +
                        '&fecha_inicio=' +
                        (filtros.fecha_inicio || '') +
                        '&fecha_fin=' +
                        (filtros.fecha_fin || '')
                    "
                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md text-sm hover:bg-purple-700 transition"
                >
                    <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
                    Exportar Excel
                </a>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtros -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6"
                >
                    <div class="flex items-center gap-2 mb-4">
                        <FunnelIcon class="h-5 w-5 text-gray-500" />
                        <h3
                            class="font-medium text-gray-900 dark:text-gray-100"
                        >
                            Filtros
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                Estado
                            </label>
                            <select
                                v-model="estado"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                            >
                                <option value="">Todos</option>
                                <option
                                    v-for="(label, value) in estados"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                Fecha Salida Desde
                            </label>
                            <input
                                v-model="fechaInicio"
                                type="date"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                Fecha Salida Hasta
                            </label>
                            <input
                                v-model="fechaFin"
                                type="date"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                            />
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="aplicarFiltros"
                                class="w-full px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition"
                            >
                                Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Ocupación Promedio
                        </p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ estadisticas.ocupacion_promedio }}%
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Viajes Llenos
                        </p>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ estadisticas.viajes_llenos }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Con Disponibilidad
                        </p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ estadisticas.viajes_disponibles }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Cupos Vendidos
                        </p>
                        <p class="text-2xl font-bold text-amber-600">
                            {{ estadisticas.cupos_vendidos }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Cupos Totales
                        </p>
                        <p
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ estadisticas.cupos_totales }}
                        </p>
                    </div>
                </div>

                <!-- Tabla de viajes -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
                >
                    <div
                        class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"
                    >
                        <h3
                            class="font-medium text-gray-900 dark:text-gray-100 flex items-center"
                        >
                            <TruckIcon class="h-5 w-5 mr-2" />
                            Detalle de Ocupación por Viaje
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Viaje / Destino
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Fechas
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Cupos
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Ocupación
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <tr
                                    v-for="viaje in viajes"
                                    :key="viaje.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td class="px-6 py-4">
                                        <div
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ viaje.plan_viaje }}
                                        </div>
                                        <div
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{ viaje.destino }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            Salida: {{ viaje.fecha_salida }}
                                        </div>
                                        <div
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            Retorno: {{ viaje.fecha_retorno }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div
                                            class="text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            {{ viaje.cupos_vendidos }} /
                                            {{ viaje.cupos_totales }}
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            {{
                                                viaje.cupos_disponibles
                                            }}
                                            disponibles
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="flex items-center justify-center"
                                        >
                                            <div class="w-full max-w-[120px]">
                                                <div
                                                    class="flex items-center justify-between text-xs mb-1"
                                                >
                                                    <span
                                                        class="text-gray-600 dark:text-gray-400"
                                                    >
                                                        {{
                                                            viaje.porcentaje_ocupacion
                                                        }}%
                                                    </span>
                                                </div>
                                                <div
                                                    class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2"
                                                >
                                                    <div
                                                        :class="[
                                                            'h-2 rounded-full transition-all',
                                                            getOcupacionColor(
                                                                viaje.porcentaje_ocupacion
                                                            ),
                                                        ]"
                                                        :style="{
                                                            width:
                                                                viaje.porcentaje_ocupacion +
                                                                '%',
                                                        }"
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                getEstadoColor(viaje.estado),
                                            ]"
                                        >
                                            {{ viaje.estado_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!viajes.length">
                                    <td
                                        colspan="5"
                                        class="px-6 py-8 text-center text-gray-400"
                                    >
                                        No hay viajes que mostrar
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Navegación -->
                <div class="mt-6">
                    <Link
                        :href="route('reportes.index')"
                        class="text-purple-600 hover:text-purple-700"
                    >
                        ← Volver a Reportes
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
