<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    Title,
    Tooltip,
    Legend,
} from "chart.js";
import { Bar, Line } from "vue-chartjs";
import { GlobeAmericasIcon, FunnelIcon } from "@heroicons/vue/24/outline";

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    Title,
    Tooltip,
    Legend
);

const props = defineProps({
    destinos: Array,
    evolucion: Object,
    filtros: Object,
});

const limite = ref(props.filtros.limite || 10);
const fechaInicio = ref(props.filtros.fecha_inicio || "");
const fechaFin = ref(props.filtros.fecha_fin || "");

const aplicarFiltros = () => {
    router.get(
        route('reportes.destinos-populares'),
        {
            limite: limite.value,
            fecha_inicio: fechaInicio.value || undefined,
            fecha_fin: fechaFin.value || undefined,
        },
        { preserveState: true }
    );
};

// Gráfico de barras horizontales
const barChartData = computed(() => ({
    labels: props.destinos.map((d) => d.nombre),
    datasets: [
        {
            label: "Ventas",
            data: props.destinos.map((d) => d.total_ventas),
            backgroundColor: [
                "rgba(16, 185, 129, 0.8)",
                "rgba(59, 130, 246, 0.8)",
                "rgba(245, 158, 11, 0.8)",
                "rgba(139, 92, 246, 0.8)",
                "rgba(239, 68, 68, 0.8)",
                "rgba(236, 72, 153, 0.8)",
                "rgba(34, 211, 238, 0.8)",
                "rgba(163, 230, 53, 0.8)",
                "rgba(251, 146, 60, 0.8)",
                "rgba(167, 139, 250, 0.8)",
            ],
            borderRadius: 8,
        },
    ],
}));

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: "y",
    plugins: {
        legend: {
            display: false,
        },
        title: {
            display: true,
            text: "Destinos más Vendidos",
        },
    },
};

// Colores para evolución
const colores = [
    "rgb(16, 185, 129)",
    "rgb(59, 130, 246)",
    "rgb(245, 158, 11)",
    "rgb(139, 92, 246)",
    "rgb(239, 68, 68)",
];

// Gráfico de evolución
const lineChartData = computed(() => ({
    labels: props.evolucion?.labels || [],
    datasets: (props.evolucion?.datasets || []).map((ds, i) => ({
        label: ds.label,
        data: ds.data,
        borderColor: colores[i % colores.length],
        backgroundColor: colores[i % colores.length] + "20",
        tension: 0.4,
        fill: false,
    })),
}));

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        title: {
            display: true,
            text: "Evolución de Ventas por Destino",
        },
    },
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value || 0);
};

const totalVentas = computed(() =>
    props.destinos.reduce((sum, d) => sum + d.total_ventas, 0)
);
const totalIngresos = computed(() =>
    props.destinos.reduce((sum, d) => sum + d.ingresos, 0)
);
</script>

<template>
    <Head title="Destinos Populares" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Reporte de Destinos Populares
            </h2>
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
                                Cantidad a mostrar
                            </label>
                            <select
                                v-model="limite"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option :value="5">Top 5</option>
                                <option :value="10">Top 10</option>
                                <option :value="15">Top 15</option>
                                <option :value="20">Top 20</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                Fecha Inicio (opcional)
                            </label>
                            <input
                                v-model="fechaInicio"
                                type="date"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                Fecha Fin (opcional)
                            </label>
                            <input
                                v-model="fechaFin"
                                type="date"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="aplicarFiltros"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                            >
                                Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas rápidas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Destinos Analizados
                        </p>
                        <p
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ destinos.length }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Total Ventas
                        </p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ totalVentas }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Ingresos Totales
                        </p>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ formatCurrency(totalIngresos) }}
                        </p>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                    >
                        <div class="h-96">
                            <Bar
                                v-if="destinos.length"
                                :data="barChartData"
                                :options="barChartOptions"
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
                        <div class="h-96">
                            <Line
                                v-if="evolucion?.datasets?.length"
                                :data="lineChartData"
                                :options="lineChartOptions"
                            />
                            <div
                                v-else
                                class="flex items-center justify-center h-full text-gray-400"
                            >
                                No hay datos de evolución disponibles
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de destinos -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
                >
                    <div
                        class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"
                    >
                        <h3
                            class="font-medium text-gray-900 dark:text-gray-100 flex items-center"
                        >
                            <GlobeAmericasIcon class="h-5 w-5 mr-2" />
                            Ranking de Destinos
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
                                        #
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Destino
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Ubicación
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Ventas
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Ingresos
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        % del Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <tr
                                    v-for="(destino, index) in destinos"
                                    :key="destino.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td class="px-6 py-4">
                                        <span
                                            :class="[
                                                'inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold',
                                                index === 0
                                                    ? 'bg-amber-100 text-amber-800'
                                                    : index === 1
                                                    ? 'bg-gray-200 text-gray-700'
                                                    : index === 2
                                                    ? 'bg-orange-100 text-orange-800'
                                                    : 'bg-gray-100 text-gray-600',
                                            ]"
                                        >
                                            {{ index + 1 }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ destino.nombre }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"
                                    >
                                        {{ destino.ubicacion }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-right text-gray-900 dark:text-gray-100"
                                    >
                                        {{ destino.total_ventas }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-right font-medium text-emerald-600"
                                    >
                                        {{ formatCurrency(destino.ingresos) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-right text-gray-600 dark:text-gray-300"
                                    >
                                        {{
                                            totalVentas
                                                ? (
                                                      (destino.total_ventas /
                                                          totalVentas) *
                                                      100
                                                  ).toFixed(1)
                                                : 0
                                        }}%
                                    </td>
                                </tr>
                                <tr v-if="!destinos.length">
                                    <td
                                        colspan="6"
                                        class="px-6 py-8 text-center text-gray-400"
                                    >
                                        No hay datos de destinos disponibles
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
                        class="text-blue-600 hover:text-blue-700"
                    >
                        ← Volver a Reportes
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
