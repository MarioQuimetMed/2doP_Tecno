<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from "chart.js";
import { Line, Bar } from "vue-chartjs";
import {
    ChartBarIcon,
    ArrowDownTrayIcon,
    FunnelIcon,
} from "@heroicons/vue/24/outline";

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const props = defineProps({
    ventas: Array,
    estadisticas: Object,
    filtros: Object,
});

const fechaInicio = ref(props.filtros.fecha_inicio);
const fechaFin = ref(props.filtros.fecha_fin);
const agrupacion = ref(props.filtros.agrupacion);

const aplicarFiltros = () => {
    router.get(
        "/reportes/ventas-periodo",
        {
            fecha_inicio: fechaInicio.value,
            fecha_fin: fechaFin.value,
            agrupacion: agrupacion.value,
        },
        { preserveState: true }
    );
};

// Gráfico de líneas
const lineChartData = computed(() => ({
    labels: props.ventas.map((v) => v.label),
    datasets: [
        {
            label: "Monto ($)",
            data: props.ventas.map((v) => v.monto),
            borderColor: "rgb(16, 185, 129)",
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            fill: true,
            tension: 0.4,
        },
    ],
}));

// Gráfico de barras
const barChartData = computed(() => ({
    labels: props.ventas.map((v) => v.label),
    datasets: [
        {
            label: "Cantidad de Ventas",
            data: props.ventas.map((v) => v.cantidad),
            backgroundColor: "rgba(59, 130, 246, 0.8)",
            borderRadius: 8,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: "top",
        },
    },
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value || 0);
};
</script>

<template>
    <Head title="Ventas por Período" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Reporte de Ventas por Período
                </h2>
                <div class="flex gap-2">
                    <a
                        :href="
                            '/reportes/exportar-ventas-excel?fecha_inicio=' +
                            fechaInicio +
                            '&fecha_fin=' +
                            fechaFin
                        "
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-700 transition"
                    >
                        <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
                        Excel
                    </a>
                    <a
                        :href="
                            '/reportes/exportar-ventas-pdf?fecha_inicio=' +
                            fechaInicio +
                            '&fecha_fin=' +
                            fechaFin
                        "
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700 transition"
                    >
                        <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
                        PDF
                    </a>
                </div>
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
                                Fecha Inicio
                            </label>
                            <input
                                v-model="fechaInicio"
                                type="date"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                Fecha Fin
                            </label>
                            <input
                                v-model="fechaFin"
                                type="date"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                Agrupar por
                            </label>
                            <select
                                v-model="agrupacion"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                <option value="dia">Día</option>
                                <option value="semana">Semana</option>
                                <option value="mes">Mes</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="aplicarFiltros"
                                class="w-full px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 transition"
                            >
                                Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Total Ventas
                        </p>
                        <p
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ estadisticas.total_ventas }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Monto Total
                        </p>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ formatCurrency(estadisticas.monto_total) }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Promedio/Venta
                        </p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ formatCurrency(estadisticas.promedio_venta) }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Completadas
                        </p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ estadisticas.completadas }}
                        </p>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                    >
                        <h3
                            class="font-medium text-gray-900 dark:text-gray-100 mb-4"
                        >
                            Ingresos por Período
                        </h3>
                        <div class="h-80">
                            <Line
                                v-if="ventas.length"
                                :data="lineChartData"
                                :options="chartOptions"
                            />
                            <div
                                v-else
                                class="flex items-center justify-center h-full text-gray-400"
                            >
                                No hay datos disponibles
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                    >
                        <h3
                            class="font-medium text-gray-900 dark:text-gray-100 mb-4"
                        >
                            Cantidad de Ventas
                        </h3>
                        <div class="h-80">
                            <Bar
                                v-if="ventas.length"
                                :data="barChartData"
                                :options="chartOptions"
                            />
                            <div
                                v-else
                                class="flex items-center justify-center h-full text-gray-400"
                            >
                                No hay datos disponibles
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de datos -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
                >
                    <div
                        class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"
                    >
                        <h3
                            class="font-medium text-gray-900 dark:text-gray-100"
                        >
                            Detalle por Período
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
                                        Período
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Cantidad
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Monto
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <tr
                                    v-for="item in ventas"
                                    :key="item.periodo"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td
                                        class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100"
                                    >
                                        {{ item.label }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-right text-gray-600 dark:text-gray-300"
                                    >
                                        {{ item.cantidad }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-right font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ formatCurrency(item.monto) }}
                                    </td>
                                </tr>
                                <tr v-if="!ventas.length">
                                    <td
                                        colspan="3"
                                        class="px-6 py-8 text-center text-gray-400"
                                    >
                                        No hay datos para el período
                                        seleccionado
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
                        class="text-emerald-600 hover:text-emerald-700"
                    >
                        ← Volver a Reportes
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
